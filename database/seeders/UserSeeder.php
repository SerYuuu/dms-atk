<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Password awal hanya untuk development. Ganti sebelum dipakai sungguhan.
        User::updateOrCreate(
            ['username' => 'pemilik'],
            ['name' => 'Pemilik Toko', 'password' => Hash::make('pemilik123'), 'role' => User::ROLE_PEMILIK]
        );

        User::updateOrCreate(
            ['username' => 'admin'],
            ['name' => 'Admin Toko', 'password' => Hash::make('admin123'), 'role' => User::ROLE_ADMIN]
        );
    }
}
