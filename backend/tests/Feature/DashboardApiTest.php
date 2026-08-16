<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_statistics_trend_notifications_and_activities(): void
    {
        $user = User::factory()->superAdmin()->create();
        Medicine::factory()->create([
            'stok' => 2,
            'stok_minimum' => 10,
            'tanggal_expired' => now()->addDays(10),
        ]);
        Sale::create([
            'user_id' => $user->id,
            'nomor_transaksi' => 'TRX-DASHBOARD-001',
            'tanggal' => now(),
            'total_harga' => 50000,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.stats.transaksi_hari_ini', 1)
            ->assertJsonStructure([
                'data' => [
                    'stats',
                    'sales_trend',
                    'notifications' => ['stok_menipis', 'expired_dekat'],
                    'activities',
                ],
            ]);

        $this->assertContains(
            50000,
            collect($response->json('data.sales_trend'))->pluck('total')->all()
        );
    }
}
