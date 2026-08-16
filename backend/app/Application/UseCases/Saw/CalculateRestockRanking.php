<?php

namespace App\Application\UseCases\Saw;

use App\Domain\Contracts\MedicineRepository;
use App\Models\Medicine;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class CalculateRestockRanking
{
    private const WEIGHTS = [
        'penjualan' => 0.30,
        'stok' => 0.25,
        'expired' => 0.20,
        'prioritas_owner' => 0.15,
        'harga' => 0.10,
    ];

    public function __construct(private readonly MedicineRepository $medicines) {}

    public function execute(?CarbonInterface $startDate = null, ?CarbonInterface $endDate = null): Collection
    {
        $startDate ??= now()->subDays(30)->startOfDay();
        $endDate ??= now()->endOfDay();
        $today = CarbonImmutable::today();

        $rows = $this->medicines
            ->rankingCandidates($startDate, $endDate)
            ->map(function (Medicine $medicine) use ($today): array {
                $expiredDate = $medicine->nearest_batch_expired
                    ? CarbonImmutable::parse($medicine->nearest_batch_expired)
                    : CarbonImmutable::parse($medicine->tanggal_expired);
                $rawDaysUntilExpired = (int) $today->diffInDays($expiredDate, false);
                $daysUntilExpired = max(1, $rawDaysUntilExpired);

                return [
                    'medicine' => $medicine,
                    'criteria' => [
                        'penjualan' => (float) ($medicine->sales_qty ?? 0),
                        'stok' => max(1, (float) $medicine->stok),
                        'expired' => (float) $daysUntilExpired,
                        'prioritas_owner' => (float) min(5, max(1, $medicine->prioritas_owner)),
                        'harga' => (float) $medicine->harga_jual,
                    ],
                    'tanggal_expired_digunakan' => $expiredDate->toDateString(),
                    'sisa_hari_expired' => $rawDaysUntilExpired,
                ];
            });

        if ($rows->isEmpty()) {
            return collect();
        }

        $max = [
            'penjualan' => max(1, $rows->max('criteria.penjualan')),
            'stok' => max(1, $rows->max('criteria.stok')),
            'expired' => max(1, $rows->max('criteria.expired')),
            'prioritas_owner' => max(1, $rows->max('criteria.prioritas_owner')),
            'harga' => max(1, $rows->max('criteria.harga')),
        ];
        $min = [
            'penjualan' => max(0, $rows->min('criteria.penjualan')),
            'stok' => max(1, $rows->min('criteria.stok')),
            'expired' => max(1, $rows->min('criteria.expired')),
            'prioritas_owner' => max(1, $rows->min('criteria.prioritas_owner')),
            'harga' => max(0, $rows->min('criteria.harga')),
        ];
        $benefitMax = [
            'penjualan' => $max['penjualan'],
            'harga' => $max['harga'],
            'prioritas_owner' => max(1, $rows->max('criteria.prioritas_owner')),
        ];
        $costMin = [
            'stok' => $min['stok'],
            'expired' => $min['expired'],
        ];

        return $rows
            ->map(function (array $row) use ($max, $min, $benefitMax, $costMin): array {
                $criteria = $row['criteria'];
                $normalized = [
                    'penjualan' => $criteria['penjualan'] / $benefitMax['penjualan'],
                    'stok' => $costMin['stok'] / $criteria['stok'],
                    'expired' => $costMin['expired'] / $criteria['expired'],
                    'prioritas_owner' => $criteria['prioritas_owner'] / $benefitMax['prioritas_owner'],
                    'harga' => $criteria['harga'] / $benefitMax['harga'],
                ];

                $weighted = collect($normalized)
                    ->mapWithKeys(fn (float $value, string $key): array => [$key => $value * self::WEIGHTS[$key]])
                    ->all();
                $score = collect($normalized)
                    ->reduce(fn (float $total, float $value, string $key): float => $total + ($value * self::WEIGHTS[$key]), 0.0);

                return [
                    ...$row,
                    'normalized' => $normalized,
                    'weighted' => $weighted,
                    'score' => $score,
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->map(function (array $row, int $index): array {
                $row['ranking'] = $index + 1;

                return $row;
            });
    }

    public function weights(): array
    {
        return self::WEIGHTS;
    }
}
