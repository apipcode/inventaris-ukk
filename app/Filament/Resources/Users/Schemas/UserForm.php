<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),
                // Password bersifat opsional saat edit — hanya diisi jika ingin ubah password
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('role')
                    ->label('Role / Hak Akses')
                    ->options(['admin' => 'Admin', 'staff' => 'Staff'])
                    ->default('staff')
                    ->required()
                    // Keamanan: Staff tidak diizinkan mengubah role user lain (maupun diri sendiri) menjadi Admin.
                    ->disabled(fn (): bool => auth()->check() && auth()->user()->role === 'staff'),
            ]);
    }
}
