<?php

namespace Tests\Unit;

use App\Application\UseCases\Saw\CalculateRestockRanking;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SawRestockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_saw_returns_ranked_restock_recommendations(): void
    {
        Medicine::factory()->create([
            'nama_obat' => 'Obat Prioritas',
            'stok' => 2,
            'harga_jual' => 100000,
            'tanggal_expired' => now()->addDays(90),
            'prioritas_owner' => 5,
        ]);

        Medicine::factory()->create([
            'nama_obat' => 'Obat Biasa',
            'stok' => 80,
            'harga_jual' => 15000,
            'tanggal_expired' => now()->addDays(180),
            'prioritas_owner' => 2,
        ]);

        $ranking = app(CalculateRestockRanking::class)->execute();

        $this->assertCount(2, $ranking);
        $this->assertSame(1, $ranking->first()['ranking']);
        $this->assertSame('Obat Prioritas', $ranking->first()['medicine']->nama_obat);
    }

    public function test_saw_uses_the_configured_criteria_weights(): void
    {
        $weights = app(CalculateRestockRanking::class)->weights();

        $this->assertSame([
            'penjualan' => 0.30,
            'stok' => 0.25,
            'expired' => 0.20,
            'prioritas_owner' => 0.15,
            'harga' => 0.10,
        ], $weights);
        $this->assertEqualsWithDelta(1.0, array_sum($weights), 0.0001);
    }

    public function test_saw_uses_nearest_batch_expired_days_as_cost_criteria(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-29 08:00:00'));

        $user = User::factory()->create();
        $medicine = Medicine::factory()->create([
            'nama_obat' => 'Obat Batch Terdekat',
            'stok' => 10,
            'harga_jual' => 10000,
            'tanggal_expired' => Carbon::today()->addDays(120),
            'prioritas_owner' => 3,
        ]);
        $purchase = Purchase::create([
            'supplier_id' => $medicine->supplier_id,
            'user_id' => $user->id,
            'nomor_faktur' => 'FKT-SAW-001',
            'tanggal_faktur' => Carbon::today(),
            'total_harga' => 0,
        ]);

        $purchase->details()->create([
            'medicine_id' => $medicine->id,
            'nomor_batch' => 'BATCH-JAUH',
            'qty' => 1,
            'harga_beli' => 7000,
            'subtotal' => 7000,
            'tanggal_expired' => Carbon::today()->addDays(60),
        ]);
        $purchase->details()->create([
            'medicine_id' => $medicine->id,
            'nomor_batch' => 'BATCH-DEKAT',
            'qty' => 1,
            'harga_beli' => 7000,
            'subtotal' => 7000,
            'tanggal_expired' => Carbon::today()->addDays(5),
        ]);

        $result = app(CalculateRestockRanking::class)->execute()->first();

        $this->assertSame(5.0, $result['criteria']['expired']);
        $this->assertSame('2026-08-03', $result['tanggal_expired_digunakan']);
        $this->assertSame(5, $result['sisa_hari_expired']);
        $this->assertArrayHasKey('weighted', $result);
        $this->assertArrayHasKey('score', $result);
    }
}
