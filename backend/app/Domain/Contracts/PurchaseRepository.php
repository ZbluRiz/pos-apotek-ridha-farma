<?php

namespace App\Domain\Contracts;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PurchaseRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function create(array $data): Purchase;

    public function update(Purchase $purchase, array $data): Purchase;

    public function delete(Purchase $purchase): void;

    public function findDetailForUpdate(int $detailId): PurchaseDetail;
}
