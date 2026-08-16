<?php

namespace App\Domain\Contracts;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SupplierRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function create(array $data): Supplier;

    public function update(Supplier $supplier, array $data): Supplier;

    public function delete(Supplier $supplier): void;

    public function count(): int;
}
