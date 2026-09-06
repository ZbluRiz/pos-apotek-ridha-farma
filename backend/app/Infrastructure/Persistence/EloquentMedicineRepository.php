<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Contracts\MedicineRepository;
use App\Models\Medicine;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentMedicineRepository implements MedicineRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return Medicine::query()
            ->with('supplier')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                ->where(function ($nested) use ($search): void {
                    $nested->where('kode_obat', 'like', "%{$search}%")
                        ->orWhere('nama_obat', 'like', "%{$search}%")
                        ->orWhere('kategori', 'like', "%{$search}%");
                }))
            ->when($filters['supplier_id'] ?? null, fn ($query, $supplierId) => $query
                ->where('supplier_id', $supplierId))
            ->when($filters['kategori'] ?? null, fn ($query, string $kategori) => $query
                ->where('kategori', $kategori))
            ->when($filters['stok_status'] ?? null, function ($query, string $status): void {
                match ($status) {
                    'menipis' => $query->lowStock(),
                    'habis' => $query->where('stok', '<=', 0),
                    'aman' => $query->whereColumn('stok', '>', 'stok_minimum'),
                    default => null,
                };
            })
            ->when($filters['expired_status'] ?? null, function ($query, string $status): void {
                match ($status) {
                    'hampir_expired' => $query->whereDate('tanggal_expired', '<=', now()->addDays(30))->whereDate('tanggal_expired', '>=', now()),
                    'expired' => $query->whereDate('tanggal_expired', '<', now()),
                    default => null,
                };
            })
            ->when($filters['low_stock'] ?? false, fn ($query) => $query->lowStock())
            ->when($filters['near_expired'] ?? false, fn ($query) => $query
                ->whereDate('tanggal_expired', '<=', now()->addDays(30)))
            ->when($filters['sort_by'] ?? 'kode_obat', function ($query, string $sortBy) use ($filters): void {
                $direction = strtolower($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                match ($sortBy) {
                    'nama_obat' => $query->orderBy('nama_obat', $direction),
                    'stok' => $query->orderBy('stok', $direction),
                    'harga_jual' => $query->orderBy('harga_jual', $direction),
                    'tanggal_expired' => $query->orderBy('tanggal_expired', $direction),
                    default => $query->orderBy('kode_obat', $direction),
                };
            })
            ->paginate($perPage);
    }

    public function create(array $data): Medicine
    {
        return Medicine::create($data)->load('supplier');
    }

    public function update(Medicine $medicine, array $data): Medicine
    {
        $medicine->update($data);

        return $medicine->refresh()->load('supplier');
    }

    public function delete(Medicine $medicine): void
    {
        $medicine->delete();
    }

    public function hasSales(Medicine $medicine): bool
    {
        return $medicine->saleDetails()->exists();
    }

    public function findForUpdate(int $id): Medicine
    {
        return Medicine::query()->lockForUpdate()->findOrFail($id);
    }

    public function rankingCandidates(CarbonInterface $startDate, CarbonInterface $endDate): Collection
    {
        return Medicine::query()
            ->with('supplier')
            ->withMin('purchaseDetails as nearest_batch_expired', 'tanggal_expired')
            ->withSum(['saleDetails as sales_qty' => fn ($query) => $query
                ->whereHas('sale', fn ($saleQuery) => $saleQuery
                    ->whereBetween('tanggal', [$startDate, $endDate])),
            ], 'qty')
            ->get();
    }

    public function lowStock(int $limit): Collection
    {
        return Medicine::query()
            ->with('supplier')
            ->lowStock()
            ->orderBy('stok')
            ->limit($limit)
            ->get();
    }

    public function nearExpired(CarbonInterface $until, int $limit): Collection
    {
        return Medicine::query()
            ->with('supplier')
            ->whereDate('tanggal_expired', '<=', $until)
            ->orderBy('tanggal_expired')
            ->limit($limit)
            ->get();
    }

    public function count(): int
    {
        return Medicine::count();
    }
}
