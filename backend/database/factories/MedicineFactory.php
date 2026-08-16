<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineFactory extends Factory
{
    public function definition(): array
    {
        $hargaBeli = fake()->numberBetween(5000, 150000);

        return [
            'supplier_id' => Supplier::factory(),
            'kode_obat' => 'OBT-'.fake()->unique()->numberBetween(1000, 9999),
            'nama_obat' => fake()->randomElement(['Paracetamol', 'Amoxicillin', 'Cetirizine', 'Omeprazole', 'Vitamin C']).' '.fake()->numberBetween(100, 1000).'mg',
            'kategori' => fake()->randomElement(['Analgesik', 'Antibiotik', 'Antihistamin', 'Vitamin', 'Pencernaan']),
            'satuan' => fake()->randomElement(['tablet', 'strip', 'botol', 'box']),
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaBeli + fake()->numberBetween(2000, 30000),
            'stok' => fake()->numberBetween(0, 100),
            'stok_minimum' => fake()->numberBetween(5, 20),
            'tanggal_expired' => fake()->dateTimeBetween('+7 days', '+18 months')->format('Y-m-d'),
            'prioritas_owner' => fake()->numberBetween(1, 5),
        ];
    }
}
