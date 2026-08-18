<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'pemilik@ridhafarma.com'],
            [
                'name' => 'Pemilik Ridha Farma',
                'password' => 'ridhafarma123',
                'role' => 'super_admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@ridhafarma.com'],
            [
                'name' => 'Kasir Ridha Farma',
                'password' => 'ridhafarma123',
                'role' => 'admin',
            ]
        );
    }
}
