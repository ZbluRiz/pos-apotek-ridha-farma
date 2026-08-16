<?php

namespace App\Application\UseCases\Sales;

use App\Domain\Contracts\MedicineRepository;
use App\Domain\Contracts\SaleRepository;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ManageSales
{
    public function __construct(
        private readonly SaleRepository $sales,
        private readonly MedicineRepository $medicines,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->sales->paginate($filters, min(max($perPage, 1), 100));
    }

    public function show(Sale $sale): Sale
    {
        return $sale->load(['user', 'details.medicine.supplier']);
    }

    public function create(array $payload, int $userId): Sale
    {
        return DB::transaction(function () use ($payload, $userId): Sale {
            $sale = $this->sales->create([
                'user_id' => $userId,
                'nomor_transaksi' => $this->generateTransactionNumber(),
                'tanggal' => $payload['tanggal'],
                'total_harga' => 0,
            ]);

            $total = $this->syncDetails($sale, $payload['details']);
            $this->sales->update($sale, ['total_harga' => $total]);

            return $sale->load(['user', 'details.medicine.supplier']);
        });
    }

    public function update(Sale $sale, array $payload): Sale
    {
        return DB::transaction(function () use ($sale, $payload): Sale {
            foreach ($sale->details as $detail) {
                $detail->medicine()->increment('stok', $detail->qty);
            }

            $sale->details()->delete();
            $this->sales->update($sale, ['tanggal' => $payload['tanggal']]);

            $total = $this->syncDetails($sale, $payload['details']);
            $this->sales->update($sale, ['total_harga' => $total]);

            return $sale->load(['user', 'details.medicine.supplier']);
        });
    }

    public function delete(Sale $sale): void
    {
        DB::transaction(function () use ($sale): void {
            foreach ($sale->details as $detail) {
                $detail->medicine()->increment('stok', $detail->qty);
            }

            $this->sales->delete($sale);
        });
    }

    private function syncDetails(Sale $sale, array $details): float
    {
        $total = 0.0;

        foreach ($details as $item) {
            $medicine = $this->medicines->findForUpdate($item['medicine_id']);

            if ($medicine->stok < $item['qty']) {
                throw ValidationException::withMessages([
                    'details' => "Stok {$medicine->nama_obat} tidak mencukupi.",
                ]);
            }

            $subtotal = (float) $medicine->harga_jual * (int) $item['qty'];
            $sale->details()->create([
                'medicine_id' => $medicine->id,
                'qty' => $item['qty'],
                'harga' => $medicine->harga_jual,
                'subtotal' => $subtotal,
            ]);

            $medicine->decrement('stok', $item['qty']);
            $total += $subtotal;
        }

        return $total;
    }

    private function generateTransactionNumber(): string
    {
        return 'TRX-'.now()->format('Ymd').'-'.Str::upper(substr((string) Str::ulid(), -8));
    }
}
