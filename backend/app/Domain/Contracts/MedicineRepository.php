<?php

namespace App\Domain\Contracts;

use App\Models\Medicine;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MedicineRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function create(array $data): Medicine;

    public function update(Medicine $medicine, array $data): Medicine;

    public function delete(Medicine $medicine): void;

    public function hasSales(Medicine $medicine): bool;

    public function findForUpdate(int $id): Medicine;

    public function rankingCandidates(CarbonInterface $startDate, CarbonInterface $endDate): Collection;

    public function lowStock(int $limit): Collection;

    public function nearExpired(CarbonInterface $until, int $limit): Collection;

    public function count(): int;
}
