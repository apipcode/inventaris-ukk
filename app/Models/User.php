<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

/**
 * Model User — Mewakili pengguna yang dapat login ke sistem.
 *
 * Terdapat dua role yang diizinkan:
 *  - 'admin' : Akses penuh ke semua fitur (Master Data, Transaksi, Pengaturan).
 *  - 'staff' : Akses terbatas, hanya dapat mencatat & melihat transaksi peminjaman.
 */

// Atribut yang boleh diisi secara massal (mass assignment)
#[Fillable(['name', 'email', 'password', 'role'])]

// Atribut yang disembunyikan dari serialisasi (misal saat to JSON/Array)
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Menentukan apakah user diizinkan mengakses panel admin Filament.
     * Hanya user dengan role 'admin' atau 'staff' yang diperbolehkan masuk.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'staff']);
    }

    /**
     * Mendefinisikan casting tipe data untuk atribut-atribut tertentu.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // Password otomatis di-hash saat disimpan ke database
            'password'          => 'hashed',
        ];
    }
}
