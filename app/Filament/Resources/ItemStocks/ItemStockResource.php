<?php

namespace App\Filament\Resources\ItemStocks;

use App\Filament\Resources\ItemStocks\Pages\CreateItemStock;
use App\Filament\Resources\ItemStocks\Pages\EditItemStock;
use App\Filament\Resources\ItemStocks\Pages\ListItemStocks;
use App\Filament\Resources\ItemStocks\Schemas\ItemStockForm;
use App\Filament\Resources\ItemStocks\Tables\ItemStocksTable;
use App\Models\ItemStock;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Resource Filament untuk mengelola Data Barang (Stok Inventaris).
 * Terletak di grup navigasi "Master Data" — hanya dapat diakses oleh Admin.
 *
 * Form  → ItemStockForm  (Schemas/ItemStockForm.php)
 * Tabel → ItemStocksTable (Tables/ItemStocksTable.php)
 */
class ItemStockResource extends Resource
{
    // Model Eloquent yang dikelola oleh resource ini
    protected static ?string $model = ItemStock::class;

    // Konfigurasi navigasi sidebar
    protected static ?string              $navigationLabel = 'Data Barang';
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?int                 $navigationSort  = 2;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    /**
     * Mendefinisikan tampilan form (Create & Edit).
     * Didelegasikan ke kelas ItemStockForm agar kode tetap modular.
     */
    public static function form(Schema $schema): Schema
    {
        return ItemStockForm::configure($schema);
    }

    /**
     * Mendefinisikan tampilan tabel (List/Index) beserta aksi-aksinya.
     * Didelegasikan ke kelas ItemStocksTable agar kode tetap modular.
     */
    public static function table(Table $table): Table
    {
        return ItemStocksTable::configure($table);
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
            'index'  => ListItemStocks::route('/'),
            'create' => CreateItemStock::route('/create'),
            'edit'   => EditItemStock::route('/{record}/edit'),
        ];
    }

    /**
     * Membatasi akses resource ini hanya untuk role 'admin'.
     * Staff tidak dapat melihat atau mengelola data stok barang secara langsung.
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }
}
