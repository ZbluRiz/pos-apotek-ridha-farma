<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Contracts\SaleRepository;
use App\Models\Sale;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentSaleRepository implements SaleRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return Sale::query()
            ->with(['user', 'details.medicine'])
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                ->where(function ($nested) use ($search): void {
                    $nested->where('nomor_transaksi', 'like', "%{$search}%")
                        ->orWhereHas('details.medicine', fn ($medicineQuery) => $medicineQuery
                            ->where('nama_obat', 'like', "%{$search}%")
                            ->orWhere('kode_obat', 'like', "%{$search}%"));
                }))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query
                ->whereDate('tanggal', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query
                ->whereDate('tanggal', '<=', $date))
            ->latest('tanggal')
            ->paginate($perPage);
    }

    public function create(array $data): Sale
    {
        return Sale::create($data);
    }

    public function update(Sale $sale, array $data): Sale
    {
        $sale->update($data);

        return $sale;
    }

    public function delete(Sale $sale): void
    {
        $sale->delete();
    }

    public function query(): Builder
    {
        return Sale::query();
    }

    public function countOnDate(CarbonInterface $date): int
    {
        return Sale::whereDate('tanggal', $date)->count();
    }

    public function sumForMonth(int $month, int $year): float
    {
        return (float) Sale::whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->sum('total_harga');
    }

    public function dailyTrend(CarbonInterface $startDate, CarbonInterface $endDate): Collection
    {
        return Sale::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('DATE(tanggal) as tanggal, SUM(total_harga) as total')
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy('tanggal')
            ->get();
    }

    public function recent(int $limit): Collection
    {
        return Sale::query()
            ->with('user')
            ->latest('tanggal')
            ->limit($limit)
            ->get();
    }
}
