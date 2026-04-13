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

class BorrowedItemResource extends Resource
{
    protected static ?string $model = BorrowedItem::class;

    protected static ?string $navigationLabel = 'Peminjaman';
    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 3;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function form(Schema $schema): Schema
    {
        return BorrowedItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BorrowedItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBorrowedItems::route('/'),
            'create' => CreateBorrowedItem::route('/create'),
            'edit' => EditBorrowedItem::route('/{record}/edit'),
        ];
    }
}
