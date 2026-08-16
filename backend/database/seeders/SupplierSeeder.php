<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'nama_supplier' => 'PT Kimia Farma Trading',
                'alamat' => 'Jl. Budi Utomo No. 1, Jakarta Pusat',
                'telepon' => '021-3456789',
            ],
            [
                'nama_supplier' => 'PT Enseval Putera Megatrading',
                'alamat' => 'Jl. Pulo Lentut No. 10, Jakarta Timur',
                'telepon' => '021-46822422',
            ],
            [
                'nama_supplier' => 'PT Anugerah Pharmindo Lestari',
                'alamat' => 'Jl. Raya Bekasi Km. 28, Jakarta Timur',
                'telepon' => '021-4608828',
            ],
            [
                'nama_supplier' => 'PT Dos Ni Roha',
                'alamat' => 'Jl. Warung Buncit Raya No. 15, Jakarta Selatan',
                'telepon' => '021-79188777',
            ],
            [
                'nama_supplier' => 'CV Sumber Sehat Medika',
                'alamat' => 'Jl. Dr. Sutomo No. 42, Bandung',
                'telepon' => '022-7213344',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['nama_supplier' => $supplier['nama_supplier']],
                $supplier
            );
        }
    }
}
