<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Contracts\SupplierRepository;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentSupplierRepository implements SupplierRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return Supplier::query()
            ->withCount('medicines')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                ->where('nama_supplier', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier->refresh();
    }

    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }

    public function count(): int
    {
        return Supplier::count();
    }
}
