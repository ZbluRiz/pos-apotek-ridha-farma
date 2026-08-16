<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurchaseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_increases_stock_and_return_decreases_stock(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'admin']);
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->for($supplier)->create([
            'stok' => 5,
            'harga_beli' => 10000,
        ]);

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/purchases', [
            'supplier_id' => $supplier->id,
            'nomor_faktur' => 'FKT-TEST-001',
            'tanggal_faktur' => now()->toDateString(),
            'file_faktur' => UploadedFile::fake()->image('faktur.jpg'),
            'details' => [
                [
                    'medicine_id' => $medicine->id,
                    'nomor_batch' => 'BATCH-001',
                    'qty' => 10,
                    'harga_beli' => 11000,
                    'tanggal_expired' => now()->subDay()->toDateString(),
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.nomor_faktur', 'FKT-TEST-001')
            ->assertJsonPath('data.total_harga', 110000);

        Storage::disk('public')->assertExists($response->json('data.file_faktur'));
        $this->assertSame(15, $medicine->fresh()->stok);

        $purchaseId = $response->json('data.id');
        $detailId = $response->json('data.details.0.id');

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/purchases/{$purchaseId}/details/{$detailId}/return", [
                'status_retur' => 'diretur',
                'qty_retur' => 4,
                'tanggal_retur' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('data.details.0.status_retur', 'diretur')
            ->assertJsonPath('data.details.0.qty_retur', 4);

        $this->assertSame(11, $medicine->fresh()->stok);
    }

    public function test_admin_can_delete_purchase_invoice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->for($supplier)->create(['stok' => 5]);

        $purchase = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/purchases', [
            'supplier_id' => $supplier->id,
            'nomor_faktur' => 'FKT-TEST-DELETE',
            'tanggal_faktur' => now()->toDateString(),
            'details' => [
                [
                    'medicine_id' => $medicine->id,
                    'qty' => 1,
                    'harga_beli' => 10000,
                    'tanggal_expired' => now()->addYear()->toDateString(),
                ],
            ],
        ]);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/purchases/'.$purchase->json('data.id'))
            ->assertOk();

        $this->assertDatabaseMissing('purchases', ['id' => $purchase->json('data.id')]);
    }
}
