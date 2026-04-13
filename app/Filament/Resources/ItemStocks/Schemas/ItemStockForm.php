<?php

namespace App\Filament\Resources\ItemStocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ItemStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori')
                    ->searchable()
                    ->required(),
                TextInput::make('item_name')
                    ->label('Nama Barang')
                    ->required(),
                TextInput::make('total_stock')
                    ->label('Jumlah Stok')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_repaired')
                    ->label('Jumlah Rusak')
                    ->required()
                    ->numeric()
                    ->default(0),
                // Field Jumlah Dipinjam bersifat otomatis/dihitung oleh sistem (Disabled).
                // Nilai ini akan berubah secara otomatis saat ada transaksi peminjaman atau pengembalian.
                TextInput::make('total_borrowed')
                    ->label('Jumlah Dipinjam')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(true),
            ]);
    }
}
