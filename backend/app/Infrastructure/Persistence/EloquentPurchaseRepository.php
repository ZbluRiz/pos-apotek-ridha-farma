<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Contracts\PurchaseRepository;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPurchaseRepository implements PurchaseRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return Purchase::query()
            ->with(['supplier', 'user', 'details.medicine'])
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                ->where(function ($nested) use ($search): void {
                    $nested->where('nomor_faktur', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery
                            ->where('nama_supplier', 'like', "%{$search}%"))
                        ->orWhereHas('details.medicine', fn ($medicineQuery) => $medicineQuery
                            ->where('nama_obat', 'like', "%{$search}%")
                            ->orWhere('kode_obat', 'like', "%{$search}%"));
                }))
            ->when($filters['supplier_id'] ?? null, fn ($query, int $supplierId) => $query
                ->where('supplier_id', $supplierId))
            ->when($filters['retur_status'] ?? null, fn ($query, string $status) => $query
                ->whereHas('details', fn ($detailQuery) => $detailQuery
                    ->where('status_retur', $status)))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query
                ->whereDate('tanggal_faktur', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query
                ->whereDate('tanggal_faktur', '<=', $date))
            ->latest('tanggal_faktur')
            ->paginate($perPage);
    }

    public function create(array $data): Purchase
    {
        return Purchase::create($data);
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        $purchase->update($data);

        return $purchase;
    }

    public function delete(Purchase $purchase): void
    {
        $purchase->delete();
    }

    public function findDetailForUpdate(int $detailId): PurchaseDetail
    {
        return PurchaseDetail::query()->lockForUpdate()->findOrFail($detailId);
    }
}
