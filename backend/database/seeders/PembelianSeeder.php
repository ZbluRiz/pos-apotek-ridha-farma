<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class PembelianSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'superadmin@apotek.test')->first();

        $faktur = [
            [
                'supplier' => 'PT Kimia Farma Trading',
                'nomor_faktur' => 'FKT-KFT-20260701-001',
                'tanggal_faktur' => '2026-07-01',
                'file_faktur' => 'arsip/faktur/FKT-KFT-20260701-001.pdf',
                'catatan' => 'Pembelian stok Analgesik, Sirup Anak, dan Obat Luar.',
                'details' => [
                    ['kode_obat' => 'OBT-001', 'nomor_batch' => 'KFT-PCT-2607A', 'qty' => 50, 'harga_beli' => 3500, 'tanggal_expired' => now()->addMonths(14)->toDateString()],
                    ['kode_obat' => 'OBT-002', 'nomor_batch' => 'KFT-ASM-2607B', 'qty' => 40, 'harga_beli' => 4500, 'tanggal_expired' => now()->addMonths(16)->toDateString()],
                    ['kode_obat' => 'OBT-003', 'nomor_batch' => 'KFT-IBU-2607A', 'qty' => 30, 'harga_beli' => 8000, 'tanggal_expired' => now()->addMonths(8)->toDateString()],
                    ['kode_obat' => 'OBT-038', 'nomor_batch' => 'KFT-SNM-2607C', 'qty' => 25, 'harga_beli' => 16000, 'tanggal_expired' => now()->addMonths(14)->toDateString()],
                    ['kode_obat' => 'OBT-046', 'nomor_batch' => 'KFT-BIO-2607A', 'qty' => 20, 'harga_beli' => 22000, 'tanggal_expired' => now()->addMonths(10)->toDateString()],
                ],
            ],
            [
                'supplier' => 'PT Enseval Putera Megatrading',
                'nomor_faktur' => 'FKT-EPM-20260705-002',
                'tanggal_faktur' => '2026-07-05',
                'file_faktur' => 'arsip/faktur/FKT-EPM-20260705-002.pdf',
                'catatan' => 'Pembelian antibiotik, obat diabetes & kolesterol.',
                'details' => [
                    ['kode_obat' => 'OBT-008', 'nomor_batch' => 'EPM-AMX-2607A', 'qty' => 45, 'harga_beli' => 11000, 'tanggal_expired' => now()->addMonths(10)->toDateString()],
                    ['kode_obat' => 'OBT-009', 'nomor_batch' => 'EPM-CFD-2607A', 'qty' => 30, 'harga_beli' => 16000, 'tanggal_expired' => now()->addMonths(11)->toDateString()],
                    ['kode_obat' => 'OBT-023', 'nomor_batch' => 'EPM-MTF-2607B', 'qty' => 50, 'harga_beli' => 9000, 'tanggal_expired' => now()->addMonths(15)->toDateString()],
                    ['kode_obat' => 'OBT-028', 'nomor_batch' => 'EPM-SMV-2607A', 'qty' => 35, 'harga_beli' => 8500, 'tanggal_expired' => now()->addMonths(12)->toDateString()],
                ],
            ],
            [
                'supplier' => 'PT Anugerah Pharmindo Lestari',
                'nomor_faktur' => 'FKT-APL-20260710-003',
                'tanggal_faktur' => '2026-07-10',
                'file_faktur' => 'arsip/faktur/FKT-APL-20260710-003.pdf',
                'catatan' => 'Restock vitamin, suplemen & hipertensi.',
                'details' => [
                    ['kode_obat' => 'OBT-006', 'nomor_batch' => 'APL-PND-2607A', 'qty' => 40, 'harga_beli' => 10000, 'tanggal_expired' => now()->addMonths(12)->toDateString()],
                    ['kode_obat' => 'OBT-025', 'nomor_batch' => 'APL-AML-2607A', 'qty' => 40, 'harga_beli' => 7500, 'tanggal_expired' => now()->addMonths(11)->toDateString()],
                    ['kode_obat' => 'OBT-041', 'nomor_batch' => 'APL-NRB-2607B', 'qty' => 30, 'harga_beli' => 38000, 'tanggal_expired' => now()->addMonths(15)->toDateString()],
                    ['kode_obat' => 'OBT-044', 'nomor_batch' => 'APL-IMB-2607A', 'qty' => 20, 'harga_beli' => 62000, 'tanggal_expired' => now()->addMonths(11)->toDateString()],
                ],
            ],
            [
                'supplier' => 'PT Dos Ni Roha',
                'nomor_faktur' => 'FKT-DNR-20260715-004',
                'tanggal_faktur' => '2026-07-15',
                'file_faktur' => 'arsip/faktur/FKT-DNR-20260715-004.pdf',
                'catatan' => 'Batch obat pencernaan & salep nyeri.',
                'details' => [
                    ['kode_obat' => 'OBT-015', 'nomor_batch' => 'DNR-OMP-2607A', 'qty' => 30, 'harga_beli' => 13000, 'tanggal_expired' => now()->addMonths(6)->toDateString()],
                    ['kode_obat' => 'OBT-016', 'nomor_batch' => 'DNR-LNS-2607B', 'qty' => 25, 'harga_beli' => 15000, 'tanggal_expired' => now()->addMonths(7)->toDateString()],
                    ['kode_obat' => 'OBT-019', 'nomor_batch' => 'DNR-LOP-2607X', 'qty' => 30, 'harga_beli' => 5000, 'tanggal_expired' => now()->addMonths(2)->toDateString(), 'qty_retur' => 5, 'status_retur' => 'diajukan_retur'],
                    ['kode_obat' => 'OBT-049', 'nomor_batch' => 'DNR-VLT-2607A', 'qty' => 15, 'harga_beli' => 58000, 'tanggal_expired' => now()->addMonths(9)->toDateString()],
                ],
            ],
            [
                'supplier' => 'CV Sumber Sehat Medika',
                'nomor_faktur' => 'FKT-SSM-20260720-005',
                'tanggal_faktur' => '2026-07-20',
                'file_faktur' => 'arsip/faktur/FKT-SSM-20260720-005.pdf',
                'catatan' => 'Pengadaan vitamin C & sirup anak.',
                'details' => [
                    ['kode_obat' => 'OBT-037', 'nomor_batch' => 'SSM-KMX-2607A', 'qty' => 100, 'harga_beli' => 1800, 'tanggal_expired' => now()->addMonths(18)->toDateString()],
                    ['kode_obat' => 'OBT-039', 'nomor_batch' => 'SSM-OBH-2607A', 'qty' => 30, 'harga_beli' => 19000, 'tanggal_expired' => now()->addMonths(12)->toDateString()],
                    ['kode_obat' => 'OBT-040', 'nomor_batch' => 'SSM-VTC-2607A', 'qty' => 50, 'harga_beli' => 20000, 'tanggal_expired' => now()->addMonths(20)->toDateString()],
                ],
            ],
        ];

        foreach ($faktur as $item) {
            $supplier = Supplier::where('nama_supplier', $item['supplier'])->first();
            if (!$supplier) {
                continue;
            }

            $details = $item['details'];
            unset($item['supplier'], $item['details']);

            $purchase = Purchase::updateOrCreate(
                ['nomor_faktur' => $item['nomor_faktur']],
                [
                    ...$item,
                    'supplier_id' => $supplier->id,
                    'user_id' => $user?->id,
                    'total_harga' => 0,
                ]
            );

            $purchase->details()->delete();
            $total = 0;

            foreach ($details as $detail) {
                $medicine = Medicine::where('kode_obat', $detail['kode_obat'])->first();
                if (!$medicine) {
                    continue;
                }

                $qty = (int) $detail['qty'];
                $hargaBeli = (float) $detail['harga_beli'];
                $subtotal = $qty * $hargaBeli;
                $total += $subtotal;

                $purchase->details()->create([
                    'medicine_id' => $medicine->id,
                    'nomor_batch' => $detail['nomor_batch'],
                    'qty' => $qty,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotal,
                    'tanggal_expired' => $detail['tanggal_expired'],
                    'qty_retur' => $detail['qty_retur'] ?? 0,
                    'status_retur' => $detail['status_retur'] ?? 'belum_retur',
                ]);
            }

            $purchase->update(['total_harga' => $total]);
        }
    }
}
