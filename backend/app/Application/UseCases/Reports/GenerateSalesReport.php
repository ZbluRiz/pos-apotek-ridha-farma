<?php

namespace App\Application\UseCases\Reports;

use App\Domain\Contracts\SaleRepository;
use App\Models\Sale;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GenerateSalesReport
{
    public function __construct(private readonly SaleRepository $sales) {}

    public function daily(CarbonInterface $date): array
    {
        return $this->summary(
            $this->sales->query()->whereDate('tanggal', $date),
            'Laporan harian '.$date->toDateString()
        );
    }

    public function monthly(int $month, int $year): array
    {
        return $this->summary(
            $this->sales->query()->whereMonth('tanggal', $month)->whereYear('tanggal', $year),
            "Laporan bulanan {$month}/{$year}"
        );
    }

    public function yearly(int $year): array
    {
        return $this->summary(
            $this->sales->query()->whereYear('tanggal', $year),
            "Laporan tahunan {$year}"
        );
    }

    private function summary(Builder $query, string $title): array
    {
        $sales = (clone $query)->with('details')->latest('tanggal')->get();

        return [
            'title' => $title,
            'total_transaksi' => $sales->count(),
            'total_penjualan' => (float) $sales->sum('total_harga'),
            'total_item_terjual' => (int) $sales->flatMap->details->sum('qty'),
            'top_medicines' => DB::table('sale_details')
                ->join('sales', 'sales.id', '=', 'sale_details.sale_id')
                ->join('medicines', 'medicines.id', '=', 'sale_details.medicine_id')
                ->whereIn('sales.id', $sales->pluck('id'))
                ->select('medicines.id', 'medicines.kode_obat', 'medicines.nama_obat', DB::raw('SUM(sale_details.qty) as qty'))
                ->groupBy('medicines.id', 'medicines.kode_obat', 'medicines.nama_obat')
                ->orderByDesc('qty')
                ->limit(5)
                ->get(),
            'sales' => $sales->map(fn (Sale $sale): array => [
                'id' => $sale->id,
                'nomor_transaksi' => $sale->nomor_transaksi,
                'tanggal' => $sale->tanggal->toISOString(),
                'total_harga' => (float) $sale->total_harga,
            ]),
        ];
    }
}
