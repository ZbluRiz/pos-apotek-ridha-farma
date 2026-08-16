<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_supplier(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/suppliers', [
                'nama_supplier' => 'Supplier Terlarang',
                'alamat' => 'Jakarta',
                'telepon' => '021123',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_create_supplier(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/suppliers', [
                'nama_supplier' => 'Supplier Resmi',
                'alamat' => 'Jakarta',
                'telepon' => '021123',
            ])
            ->assertCreated();
    }

    public function test_admin_can_delete_historical_sale(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sale = Sale::create([
            'user_id' => $admin->id,
            'nomor_transaksi' => 'TRX-TEST-001',
            'tanggal' => now(),
            'total_harga' => 0,
        ]);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/sales/{$sale->id}")
            ->assertOk();

        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
    }

    public function test_admin_can_manage_medicines(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = Supplier::factory()->create();
        $medicine = Medicine::factory()->for($supplier)->create();

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/medicines/{$medicine->id}", [
                'supplier_id' => $supplier->id,
                'kode_obat' => $medicine->kode_obat,
                'nama_obat' => 'Obat Diperbarui',
                'kategori' => $medicine->kategori,
                'satuan' => $medicine->satuan,
                'harga_beli' => 10000,
                'harga_jual' => 15000,
                'stok' => 20,
                'stok_minimum' => 5,
                'tanggal_expired' => now()->addYear()->toDateString(),
                'prioritas_owner' => 4,
            ])
            ->assertOk()
            ->assertJsonPath('data.nama_obat', 'Obat Diperbarui');
    }
}
