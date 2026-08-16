<?php

namespace App\Application\UseCases\Dashboard;

use App\Domain\Contracts\MedicineRepository;
use App\Domain\Contracts\SaleRepository;
use App\Domain\Contracts\SupplierRepository;
use App\Models\Medicine;
use App\Models\Sale;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class GetDashboard
{
    public function __construct(
        private readonly MedicineRepository $medicines,
        private readonly SupplierRepository $suppliers,
        private readonly SaleRepository $sales,
    ) {}

    public function execute(): array
    {
        $lowStock = $this->medicines->lowStock(6);
        $nearExpired = $this->medicines->nearExpired(now()->addDays(30), 6);

        return [
            'stats' => [
                'total_obat' => $this->medicines->count(),
                'total_supplier' => $this->suppliers->count(),
                'transaksi_hari_ini' => $this->sales->countOnDate(today()),
                'penjualan_bulan_ini' => $this->sales->sumForMonth(now()->month, now()->year),
            ],
            'sales_trend' => $this->salesTrend(),
            'notifications' => [
                'stok_menipis' => $lowStock,
                'expired_dekat' => $nearExpired,
            ],
            'activities' => $this->activities($lowStock, $nearExpired),
        ];
    }

    private function salesTrend(): array
    {
        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();
        $totals = $this->sales->dailyTrend($startDate, $endDate)
            ->keyBy(fn ($row): string => $row->tanggal->toDateString());

        return collect(CarbonPeriod::create($startDate, $endDate))
            ->map(function ($date) use ($totals): array {
                $dateString = $date->toDateString();

                return [
                    'date' => $dateString,
                    'label' => $date->locale('id')->translatedFormat('j M'),
                    'total' => (float) ($totals->get($dateString)?->total ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function activities(Collection $lowStock, Collection $nearExpired): array
    {
        $sales = $this->sales->recent(3)->map(fn (Sale $sale): array => [
            'id' => "sale-{$sale->id}",
            'type' => 'sale',
            'title' => 'Penjualan baru senilai Rp '.number_format((float) $sale->total_harga, 0, ',', '.'),
            'description' => 'Kasir: '.($sale->user?->name ?? 'User terhapus'),
            'occurred_at' => $sale->tanggal->toISOString(),
        ]);

        $stockActivities = $lowStock->take(2)->map(fn (Medicine $medicine): array => [
            'id' => "stock-{$medicine->id}",
            'type' => 'stock',
            'title' => "Stok menipis: {$medicine->nama_obat}",
            'description' => "Sisa {$medicine->stok} {$medicine->satuan}",
            'occurred_at' => $medicine->updated_at->toISOString(),
        ]);

        $expiredActivities = $nearExpired->take(2)->map(fn (Medicine $medicine): array => [
            'id' => "expired-{$medicine->id}",
            'type' => 'expired',
            'title' => "Produk akan kedaluwarsa: {$medicine->nama_obat}",
            'description' => 'Exp: '.$medicine->tanggal_expired->locale('id')->translatedFormat('j M Y'),
            'occurred_at' => $medicine->updated_at->toISOString(),
        ]);

        return $sales
            ->concat($stockActivities)
            ->concat($expiredActivities)
            ->sortByDesc('occurred_at')
            ->take(6)
            ->values()
            ->all();
    }
}
