<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_decreases_medicine_stock(): void
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['stok' => 10, 'harga_jual' => 20000]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/sales', [
            'tanggal' => now()->toISOString(),
            'details' => [
                ['medicine_id' => $medicine->id, 'qty' => 3],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.total_harga', 60000);

        $this->assertSame(7, $medicine->fresh()->stok);
    }

    public function test_sales_can_be_searched_by_medicine_name(): void
    {
        $user = User::factory()->create();
        $matchedMedicine = Medicine::factory()->create([
            'nama_obat' => 'Obat Search Khusus',
            'stok' => 10,
            'harga_jual' => 12000,
        ]);
        $otherMedicine = Medicine::factory()->create([
            'nama_obat' => 'Obat Lain',
            'stok' => 10,
            'harga_jual' => 15000,
        ]);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/sales', [
            'tanggal' => now()->toISOString(),
            'details' => [
                ['medicine_id' => $matchedMedicine->id, 'qty' => 1],
            ],
        ])->assertCreated();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/sales', [
            'tanggal' => now()->toISOString(),
            'details' => [
                ['medicine_id' => $otherMedicine->id, 'qty' => 1],
            ],
        ])->assertCreated();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/sales?search=Search%20Khusus')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.details.0.medicine.nama_obat', 'Obat Search Khusus');
    }
}
