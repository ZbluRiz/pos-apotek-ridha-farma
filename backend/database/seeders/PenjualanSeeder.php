<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanSeeder extends Seeder
{
    public function run(): void
    {
        // Get an existing user (kasir) to attach the sales to
        $user = User::where('email', 'kasir@ridhafarma.com')->first();
        
        if (!$user) {
            $this->command->error('User kasir@ridhafarma.com belum ada. Harap jalankan UserSeeder terlebih dahulu.');
            return;
        }

        DB::transaction(function () use ($user): void {
            $this->restoreStockFromExistingSales();
            Sale::query()->delete();

            // Medicines sales distribution pattern (kode_obat => relative daily demand weight)
            $salesWeights = [
                'OBT-001' => 35, // Paracetamol 500mg (Fast moving)
                'OBT-002' => 25, // Asam Mefenamat
                'OBT-003' => 18, // Ibuprofen
                'OBT-004' => 20, // Bodrex
                'OBT-005' => 22, // Paramex
                'OBT-006' => 30, // Panadol Extra (Fast moving)
                'OBT-007' => 12, // Antalgin
                'OBT-008' => 28, // Amoxicillin (Fast moving)
                'OBT-009' => 24, // Cefadroxil (Fast moving)
                'OBT-010' => 15, // Ciprofloxacin
                'OBT-011' => 26, // FG Troches (Fast moving)
                'OBT-012' => 20, // Cetirizine
                'OBT-013' => 14, // CTM
                'OBT-014' => 16, // Loratadine
                'OBT-015' => 22, // Omeprazole
                'OBT-016' => 19, // Lansoprazole
                'OBT-017' => 18, // Antasida Doen
                'OBT-018' => 27, // Promag (Fast moving)
                'OBT-019' => 10, // Loperamide
                'OBT-020' => 15, // Diapet
                'OBT-021' => 17, // Entrostop
                'OBT-022' => 14, // Ranitidine
                'OBT-023' => 25, // Metformin (Fast moving)
                'OBT-024' => 12, // Glibenclamide
                'OBT-025' => 30, // Amlodipine 5mg (Fast moving)
                'OBT-026' => 24, // Amlodipine 10mg (Fast moving)
                'OBT-027' => 15, // Captopril
                'OBT-028' => 22, // Simvastatin 10mg
                'OBT-029' => 16, // Simvastatin 20mg
                'OBT-030' => 14, // Allopurinol
                'OBT-031' => 12, // Salbutamol
                'OBT-032' => 8,  // Ventolin Inhaler
                'OBT-033' => 15, // Ambroxol
                'OBT-034' => 25, // Mixagrip (Fast moving)
                'OBT-035' => 18, // Tremenza
                'OBT-036' => 14, // Rhinos SR
                'OBT-037' => 30, // Komix (Fast moving)
                'OBT-038' => 28, // Sanmol Sirup (Fast moving)
                'OBT-039' => 22, // OBH Combi Anak
                'OBT-040' => 20, // Vitamin C
                'OBT-041' => 25, // Neurobion Forte (Fast moving)
                'OBT-042' => 18, // Sangobion
                'OBT-043' => 22, // Tolak Angin
                'OBT-044' => 16, // Imboost Force
                'OBT-045' => 12, // CDR
                'OBT-046' => 24, // Bioplacenton (Fast moving)
                'OBT-047' => 15, // Betadine
                'OBT-048' => 14, // Counterpain
                'OBT-049' => 12, // Voltaren
                'OBT-050' => 18, // Insto
                'OBT-051' => 14, // Rohto
                'OBT-052' => 16, // Dexamethasone
            ];

            $allMedicines = Medicine::all()->keyBy('kode_obat');

            // Generate exactly 50 transactions for July 2026
            $trxDates = collect();
            for ($i = 0; $i < 50; $i++) {
                $day = rand(1, 31);
                $hour = rand(8, 20); // between 8 AM and 8 PM
                $minute = rand(0, 59);
                $second = rand(0, 59);
                $trxDates->push(Carbon::create(2026, 7, $day, $hour, $minute, $second));
            }
            
            // Sort by date so transactions are in chronological order
            $trxDates = $trxDates->sort()->values();

            $trxCounter = 1;

            foreach ($trxDates as $saleTime) {
                $sale = Sale::create([
                    'user_id' => $user->id,
                    'nomor_transaksi' => 'TRX-' . $saleTime->format('Ymd') . '-' . str_pad((string) $trxCounter++, 4, '0', STR_PAD_LEFT),
                    'tanggal' => $saleTime,
                    'total_harga' => 0,
                ]);

                $total = 0;

                // Select 1 to 4 random distinct medicines for this transaction
                $selectedCodes = collect(array_keys($salesWeights))->random(rand(1, 4));

                foreach ($selectedCodes as $kodeObat) {
                    $medicine = $allMedicines->get($kodeObat);
                    if (! $medicine || $medicine->stok <= 1) {
                        continue;
                    }

                    // Determine sales quantity based on weight
                    $weight = $salesWeights[$kodeObat] ?? 10;
                    $baseQty = rand(1, 3);
                    if ($weight >= 25 && rand(1, 100) <= 60) {
                        $baseQty += rand(1, 2);
                    }

                    $qty = min($baseQty, max(1, (int) floor($medicine->stok / 2)));

                    $subtotal = $qty * (float) $medicine->harga_jual;

                    $sale->details()->create([
                        'medicine_id' => $medicine->id,
                        'qty' => $qty,
                        'harga' => $medicine->harga_jual,
                        'subtotal' => $subtotal,
                    ]);

                    $medicine->decrement('stok', $qty);
                    $total += $subtotal;
                }

                $sale->update(['total_harga' => $total]);
            }
        });
    }

    private function restoreStockFromExistingSales(): void
    {
        Sale::with('details.medicine')->get()->each(function (Sale $sale): void {
            foreach ($sale->details as $detail) {
                $detail->medicine?->increment('stok', $detail->qty);
            }
        });
    }
}
