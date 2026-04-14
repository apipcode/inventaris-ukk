<?php

namespace App\Filament\Resources\BorrowedItems;

use App\Filament\Resources\BorrowedItems\Pages\CreateBorrowedItem;
use App\Filament\Resources\BorrowedItems\Pages\EditBorrowedItem;
use App\Filament\Resources\BorrowedItems\Pages\ListBorrowedItems;
use App\Filament\Resources\BorrowedItems\Schemas\BorrowedItemForm;
use App\Filament\Resources\BorrowedItems\Tables\BorrowedItemsTable;
use App\Models\BorrowedItem;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Resource Filament untuk mengelola transaksi Peminjaman Barang.
 * Terletak di grup navigasi "Transaksi" di sidebar panel admin.
 *
 * Form  → BorrowedItemForm  (Schemas/BorrowedItemForm.php)
 * Tabel → BorrowedItemsTable (Tables/BorrowedItemsTable.php)
 */
class BorrowedItemResource extends Resource
{
    // Model Eloquent yang dikelola oleh resource ini
    protected static ?string $model = BorrowedItem::class;

    // Konfigurasi navigasi sidebar
    protected static ?string              $navigationLabel = 'Peminjaman';
    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?int                 $navigationSort  = 3;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    /**
     * Mendefinisikan tampilan form (Create & Edit).
     * Didelegasikan ke kelas BorrowedItemForm agar kode tetap modular.
     */
    public static function form(Schema $schema): Schema
    {
        return BorrowedItemForm::configure($schema);
    }

    /**
     * Mendefinisikan tampilan tabel (List/Index) beserta aksi-aksinya.
     * Didelegasikan ke kelas BorrowedItemsTable agar kode tetap modular.
     */
    public static function table(Table $table): Table
    {
        return BorrowedItemsTable::configure($table);
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
            'index'  => ListBorrowedItems::route('/'),
            'create' => CreateBorrowedItem::route('/create'),
            'edit'   => EditBorrowedItem::route('/{record}/edit'),
        ];
    }
}
