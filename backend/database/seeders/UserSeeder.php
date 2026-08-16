<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@apotek.test'],
            [
                'name' => 'Super Admin',
                'password' => 'password123',
                'role' => 'super_admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@apotek.test'],
            [
                'name' => 'Admin Apotek',
                'password' => 'password123',
                'role' => 'admin',
            ]
        );
    }
}
