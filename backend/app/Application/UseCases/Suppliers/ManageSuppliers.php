<?php

namespace App\Application\UseCases\Suppliers;

use App\Domain\Contracts\SupplierRepository;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ManageSuppliers
{
    public function __construct(private readonly SupplierRepository $suppliers) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->suppliers->paginate($filters, min(max($perPage, 1), 100));
    }

    public function create(array $data): Supplier
    {
        return $this->suppliers->create($data);
    }

    public function show(Supplier $supplier): Supplier
    {
        return $supplier->loadCount('medicines');
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        return $this->suppliers->update($supplier, $data);
    }

    public function delete(Supplier $supplier): void
    {
        $this->suppliers->delete($supplier);
    }
}
