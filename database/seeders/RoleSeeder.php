<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun contoh untuk tiap peran. Aman dijalankan berulang: akun yang sudah ada
 * (berdasarkan email) tidak diubah, jadi kata sandi yang sudah kamu ganti tetap aman.
 *
 * Jalankan: php artisan db:seed --class=RoleSeeder
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['Owner Toko', 'owner@example.com', User::ROLE_OWNER],
            ['Admin Toko', 'admin@example.com', User::ROLE_ADMIN],
            ['Staff Toko', 'staff@example.com', User::ROLE_STAFF],
            ['Pelanggan Contoh', 'pelanggan@example.com', User::ROLE_CUSTOMER],
        ];

        foreach ($accounts as [$name, $email, $role]) {
            User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'role' => $role, 'password' => Hash::make('password123')],
            );
        }
    }
}
