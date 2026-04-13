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

class ItemStockResource extends Resource
{
    protected static ?string $model = ItemStock::class;

    // Label navigasi menu sidebar
    protected static ?string $navigationLabel = 'Data Barang';
    
    // Kelompok menu navigasi di sidebar
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';
    
    // Urutan posisi menu di sidebar
    protected static ?int $navigationSort = 2;
    
    // Icon yang digunakan pada menu sidebar
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    public static function form(Schema $schema): Schema
    {
        return ItemStockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemStocksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemStocks::route('/'),
            'create' => CreateItemStock::route('/create'),
            'edit' => EditItemStock::route('/{record}/edit'),
        ];
    }

    // Hanya admin yang bisa mengakses halaman ini
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }
}
