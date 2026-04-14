<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Kelas Form untuk resource User.
 *
 * Mendefinisikan field input di halaman Create & Edit pengguna.
 *
 * Aturan keamanan penting:
 *  - Field password opsional saat Edit (hanya diisi jika ingin ganti password).
 *  - Field role di-disable untuk Staff (Staff tidak bisa mengubah hak akses).
 */
class UserForm
{
    /**
     * Mengkonfigurasi dan mengembalikan skema form pengguna.
     *
     * @param Schema $schema Objek skema Filament yang akan dikonfigurasi.
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // Input nama lengkap pengguna
            TextInput::make('name')
                ->label('Nama Lengkap')
                ->required(),

            // Input email (harus valid & unik di seluruh tabel users)
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->unique(ignoreRecord: true) // Validasi unik, kecuali record yang sedang diedit
                ->required(),

            // Input password — opsional saat Edit, wajib saat Create
            // dehydrated: hanya dikirim ke server jika field tidak kosong (mencegah password dikosongkan saat edit)
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->required(fn (string $operation): bool => $operation === 'create'),

            // Dropdown pilih role/hak akses pengguna
            Select::make('role')
                ->label('Role / Hak Akses')
                ->options(['admin' => 'Admin', 'staff' => 'Staff'])
                ->default('staff')
                ->required()
                // KEAMANAN: Staff tidak diizinkan mengubah role (milik diri sendiri maupun orang lain)
                ->disabled(fn (): bool => auth()->check() && auth()->user()->role === 'staff'),
        ]);
    }
}
