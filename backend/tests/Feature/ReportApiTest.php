<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_report_returns_summary_top_medicines_and_kasir(): void
    {
        $user = User::factory()->create(['name' => 'Ahmad Kasir']);
        $medicine1 = Medicine::factory()->create(['nama_obat' => 'Paracetamol 500mg', 'stok' => 50, 'harga_jual' => 5000]);
        $medicine2 = Medicine::factory()->create(['nama_obat' => 'Amoxicillin 500mg', 'stok' => 50, 'harga_jual' => 10000]);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/sales', [
            'tanggal' => now()->toDateString(),
            'details' => [
                ['medicine_id' => $medicine1->id, 'qty' => 5],
                ['medicine_id' => $medicine2->id, 'qty' => 2],
            ],
        ])->assertCreated();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/reports/daily?date=' . now()->toDateString());

        $response->assertOk()
            ->assertJsonPath('data.total_transaksi', 1)
            ->assertJsonPath('data.total_penjualan', 45000)
            ->assertJsonPath('data.total_item_terjual', 7)
            ->assertJsonPath('data.sales.0.kasir', 'Ahmad Kasir')
            ->assertJsonPath('data.sales.0.jumlah_item', 7);

        $this->assertCount(2, $response->json('data.top_medicines'));
    }

    public function test_monthly_and_yearly_report_endpoints(): void
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['stok' => 20, 'harga_jual' => 15000]);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/sales', [
            'tanggal' => now()->toDateString(),
            'details' => [
                ['medicine_id' => $medicine->id, 'qty' => 2],
            ],
        ])->assertCreated();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/reports/monthly?month=' . now()->month . '&year=' . now()->year)
            ->assertOk()
            ->assertJsonPath('data.total_transaksi', 1)
            ->assertJsonPath('data.total_penjualan', 30000);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/reports/yearly?year=' . now()->year)
            ->assertOk()
            ->assertJsonPath('data.total_transaksi', 1)
            ->assertJsonPath('data.total_penjualan', 30000);
    }
}
