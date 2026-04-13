<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // Label menu yang muncul di sidebar
    protected static ?string $navigationLabel = 'Pengguna';
    
    // Grup menu navigasi
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';
    
    // Urutan menu di sidebar
    protected static ?int $navigationSort = 4;
    
    // Icon menu (Users)
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Memfilter data user yang muncul di tabel berdasarkan role.
     * Logika: Jika user yang login adalah 'Staff', maka dia hanya bisa melihat daftar user yang juga 'Staff'.
     * Admin tetap bisa melihat semua user (Admin & Staff).
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && auth()->user()->role === 'staff') {
            $query->where('role', 'staff');
        }

        return $query;
    }
}
