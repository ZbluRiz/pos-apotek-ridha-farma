<?php

namespace App\Application\UseCases\Purchases;

use App\Domain\Contracts\MedicineRepository;
use App\Domain\Contracts\PurchaseRepository;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManagePurchases
{
    public function __construct(
        private readonly PurchaseRepository $purchases,
        private readonly MedicineRepository $medicines,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->purchases->paginate($filters, min(max($perPage, 1), 100));
    }

    public function show(Purchase $purchase): Purchase
    {
        return $purchase->load(['supplier', 'user', 'details.medicine.supplier']);
    }

    public function create(array $payload, int $userId): Purchase
    {
        return DB::transaction(function () use ($payload, $userId): Purchase {
            $purchase = $this->purchases->create([
                'supplier_id' => $payload['supplier_id'],
                'user_id' => $userId,
                'nomor_faktur' => $payload['nomor_faktur'],
                'tanggal_faktur' => $payload['tanggal_faktur'],
                'total_harga' => 0,
                'file_faktur' => $payload['file_faktur'] ?? null,
                'catatan' => $payload['catatan'] ?? null,
            ]);

            $total = $this->syncDetails($purchase, $payload['details']);
            $this->purchases->update($purchase, ['total_harga' => $total]);

            return $this->show($purchase);
        });
    }

    public function update(Purchase $purchase, array $payload): Purchase
    {
        return DB::transaction(function () use ($purchase, $payload): Purchase {
            $purchase->load('details');
            $this->reverseStockImpact($purchase);
            $purchase->details()->delete();

            $this->purchases->update($purchase, [
                'supplier_id' => $payload['supplier_id'],
                'nomor_faktur' => $payload['nomor_faktur'],
                'tanggal_faktur' => $payload['tanggal_faktur'],
                'file_faktur' => $payload['file_faktur'] ?? $purchase->file_faktur,
                'catatan' => $payload['catatan'] ?? null,
            ]);

            $total = $this->syncDetails($purchase, $payload['details']);
            $this->purchases->update($purchase, ['total_harga' => $total]);

            return $this->show($purchase);
        });
    }

    public function delete(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase): void {
            $purchase->load('details');
            $this->reverseStockImpact($purchase);
            $this->purchases->delete($purchase);
        });
    }

    public function markReturn(Purchase $purchase, PurchaseDetail $purchaseDetail, array $payload): Purchase
    {
        return DB::transaction(function () use ($purchase, $purchaseDetail, $payload): Purchase {
            $detail = $this->purchases->findDetailForUpdate($purchaseDetail->id);

            if ($detail->purchase_id !== $purchase->id) {
                throw ValidationException::withMessages([
                    'detail' => 'Detail faktur tidak sesuai dengan faktur pembelian.',
                ]);
            }

            $qtyRetur = (int) ($payload['qty_retur'] ?? 0);
            if ($qtyRetur > $detail->qty) {
                throw ValidationException::withMessages([
                    'qty_retur' => 'Qty retur tidak boleh melebihi qty pembelian.',
                ]);
            }

            $oldReturnedQty = $detail->status_retur === 'diretur' ? $detail->qty_retur : 0;
            $newReturnedQty = $payload['status_retur'] === 'diretur' ? $qtyRetur : 0;
            $stockDelta = $newReturnedQty - $oldReturnedQty;

            if ($stockDelta !== 0) {
                $medicine = $this->medicines->findForUpdate($detail->medicine_id);

                if ($stockDelta > 0 && $medicine->stok < $stockDelta) {
                    throw ValidationException::withMessages([
                        'qty_retur' => "Stok {$medicine->nama_obat} tidak cukup untuk retur.",
                    ]);
                }

                $stockDelta > 0
                    ? $medicine->decrement('stok', $stockDelta)
                    : $medicine->increment('stok', abs($stockDelta));
            }

            $detail->update([
                'qty_retur' => $qtyRetur,
                'status_retur' => $payload['status_retur'],
                'tanggal_retur' => $payload['tanggal_retur'] ?? null,
                'catatan_retur' => $payload['catatan_retur'] ?? null,
            ]);

            return $this->show($purchase->refresh());
        });
    }

    private function syncDetails(Purchase $purchase, array $details): float
    {
        $total = 0.0;

        foreach ($details as $item) {
            $medicine = $this->medicines->findForUpdate($item['medicine_id']);
            $qty = (int) $item['qty'];
            $hargaBeli = (float) $item['harga_beli'];
            $subtotal = $qty * $hargaBeli;

            $purchase->details()->create([
                'medicine_id' => $medicine->id,
                'nomor_batch' => $item['nomor_batch'] ?? null,
                'qty' => $qty,
                'harga_beli' => $hargaBeli,
                'subtotal' => $subtotal,
                'tanggal_expired' => $item['tanggal_expired'],
            ]);

            $medicine->increment('stok', $qty);
            $medicine->update([
                'supplier_id' => $purchase->supplier_id,
                'harga_beli' => $hargaBeli,
                'tanggal_expired' => $item['tanggal_expired'],
            ]);

            $total += $subtotal;
        }

        return $total;
    }

    private function reverseStockImpact(Purchase $purchase): void
    {
        foreach ($purchase->details as $detail) {
            $remainingStockImpact = $detail->qty - ($detail->status_retur === 'diretur' ? $detail->qty_retur : 0);
            if ($remainingStockImpact <= 0) {
                continue;
            }

            $medicine = $this->medicines->findForUpdate($detail->medicine_id);
            if ($medicine->stok < $remainingStockImpact) {
                throw ValidationException::withMessages([
                    'details' => "Stok {$medicine->nama_obat} tidak cukup untuk membatalkan faktur.",
                ]);
            }

            $medicine->decrement('stok', $remainingStockImpact);
        }
    }
}
