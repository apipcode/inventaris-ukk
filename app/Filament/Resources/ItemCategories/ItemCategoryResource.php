<?php

namespace App\Filament\Resources\ItemCategories;

use App\Filament\Resources\ItemCategories\Pages\CreateItemCategory;
use App\Filament\Resources\ItemCategories\Pages\EditItemCategory;
use App\Filament\Resources\ItemCategories\Pages\ListItemCategories;
use App\Filament\Resources\ItemCategories\Schemas\ItemCategoryForm;
use App\Filament\Resources\ItemCategories\Tables\ItemCategoriesTable;
use App\Models\ItemCategory;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Resource Filament untuk mengelola Kategori Barang.
 * Terletak di grup navigasi "Master Data" — hanya dapat diakses oleh Admin.
 *
 * Form  → ItemCategoryForm  (Schemas/ItemCategoryForm.php)
 * Tabel → ItemCategoriesTable (Tables/ItemCategoriesTable.php)
 */
class ItemCategoryResource extends Resource
{
    // Model Eloquent yang dikelola oleh resource ini
    protected static ?string $model = ItemCategory::class;

    // Konfigurasi navigasi sidebar
    protected static ?string              $navigationLabel = 'Kategori Barang';
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?int                 $navigationSort  = 1;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    /**
     * Mendefinisikan tampilan form (Create & Edit).
     * Didelegasikan ke kelas ItemCategoryForm agar kode tetap modular.
     */
    public static function form(Schema $schema): Schema
    {
        return ItemCategoryForm::configure($schema);
    }

    /**
     * Mendefinisikan tampilan tabel (List/Index) beserta aksi-aksinya.
     * Didelegasikan ke kelas ItemCategoriesTable agar kode tetap modular.
     */
    public static function table(Table $table): Table
    {
        return ItemCategoriesTable::configure($table);
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
            'index'  => ListItemCategories::route('/'),
            'create' => CreateItemCategory::route('/create'),
            'edit'   => EditItemCategory::route('/{record}/edit'),
        ];
    }

    /**
     * Membatasi akses resource ini hanya untuk role 'admin'.
     * Staff tidak dapat melihat atau mengelola data kategori barang.
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }
}
