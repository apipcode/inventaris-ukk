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

/**
 * Resource Filament untuk mengelola data Pengguna (User Management).
 * Terletak di grup navigasi "Pengaturan".
 *
 * Aturan akses berdasarkan role:
 *  - Admin  : Dapat melihat & mengelola semua user (Admin & Staff).
 *  - Staff  : Hanya dapat melihat & mengelola sesama Staff (tidak bisa mengubah role).
 *
 * Form  → UserForm  (Schemas/UserForm.php)
 * Tabel → UsersTable (Tables/UsersTable.php)
 */
class UserResource extends Resource
{
    // Model Eloquent yang dikelola oleh resource ini
    protected static ?string $model = User::class;

    // Konfigurasi navigasi sidebar
    protected static ?string              $navigationLabel = 'Pengguna';
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?int                 $navigationSort  = 4;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    /**
     * Mendefinisikan tampilan form (Create & Edit).
     * Didelegasikan ke kelas UserForm agar kode tetap modular.
     */
    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    /**
     * Mendefinisikan tampilan tabel (List/Index) beserta aksi-aksinya.
     * Didelegasikan ke kelas UsersTable agar kode tetap modular.
     */
    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    /**
     * Mengembalikan daftar Relation Manager (tidak digunakan di resource ini).
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Mendaftarkan route & halaman CRUD untuk resource ini.
     */
    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Memfilter query berdasarkan role pengguna yang sedang login.
     *
     * Jika yang login adalah 'staff', query dibatasi hanya menampilkan user ber-role 'staff'.
     * Jika yang login adalah 'admin', semua user (admin & staff) ditampilkan.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Staff hanya melihat sesama Staff (tidak bisa melihat data Admin)
        if (auth()->check() && auth()->user()->role === 'staff') {
            $query->where('role', 'staff');
        }

        return $query;
    }
}
