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

class ItemCategoryResource extends Resource
{
    protected static ?string $model = ItemCategory::class;

    // Label navigasi yang muncul di menu sidebar
    protected static ?string $navigationLabel = 'Kategori Barang';
    
    // Nama grup menu navigasi (Master Data)
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';
    
    // Urutan posisi menu di sidebar
    protected static ?int $navigationSort = 1;
    
    // Icon menu menggunakan Heroicons
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    public static function form(Schema $schema): Schema
    {
        return ItemCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemCategories::route('/'),
            'create' => CreateItemCategory::route('/create'),
            'edit' => EditItemCategory::route('/{record}/edit'),
        ];
    }

    /**
     * Membatasi akses menu ini.
     * Hanya pengguna dengan role 'admin' yang dapat mengelola kategori barang.
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }
}
