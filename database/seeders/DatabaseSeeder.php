<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder — Mengisi database dengan data awal (initial data).
 *
 * Dijalankan dengan perintah: php artisan db:seed
 * Atau bersamaan migrate: php artisan migrate --seed
 *
 * Seeder ini membuat dua akun pengguna default:
 *  1. Administrator — akses penuh ke semua fitur.
 *  2. Staff Operator — akses terbatas ke fitur pencatatan peminjaman.
 *
 * PENTING: Ubah password default setelah pertama kali login di production!
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi database dengan data awal yang diperlukan aplikasi.
     */
    public function run(): void
    {
        // Buat akun Administrator (akses penuh)
        User::factory()->create([
            'name'     => 'Administrator',
            'email'    => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Buat akun Staff Operator (akses terbatas)
        User::factory()->create([
            'name'     => 'Staff Operator',
            'email'    => 'staff@staff.com',
            'password' => Hash::make('password'),
            'role'     => 'staff',
        ]);
    }
}
