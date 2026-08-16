<?php

namespace App\Domain\Contracts;

use App\Models\Sale;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface SaleRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function create(array $data): Sale;

    public function update(Sale $sale, array $data): Sale;

    public function delete(Sale $sale): void;

    public function query(): Builder;

    public function countOnDate(CarbonInterface $date): int;

    public function sumForMonth(int $month, int $year): float;

    public function dailyTrend(CarbonInterface $startDate, CarbonInterface $endDate): Collection;

    public function recent(int $limit): Collection;
}
