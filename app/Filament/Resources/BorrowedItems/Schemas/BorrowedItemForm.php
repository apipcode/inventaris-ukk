<?php

namespace App\Filament\Resources\BorrowedItems\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BorrowedItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // staff_id diisi secara otomatis dengan ID user yang sedang login (Staff yang mencatat).
                \Filament\Forms\Components\Hidden::make('staff_id')
                    ->default(fn () => auth()->id()),
                Select::make('item_id')
                    ->relationship('item', 'item_name')
                    ->label('Barang')
                    ->searchable()
                    ->required(),
                TextInput::make('total_item')
                    ->label('Jumlah Pinjam')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(function (callable $get) {
                        $itemId = $get('item_id');
                        if (!$itemId) return 0;
                        return \App\Models\ItemStock::find($itemId)?->total_stock ?? 0;
                    })
                    ->helperText(function (callable $get) {
                        $itemId = $get('item_id');
                        if (!$itemId) return null;
                        $stock = \App\Models\ItemStock::find($itemId)?->total_stock ?? 0;
                        return "Stok tersedia: {$stock}";
                    }),
                TextInput::make('name_of_borrower')
                    ->label('Nama Peminjam')
                    ->required(),
                DateTimePicker::make('date')
                    ->label('Tanggal Pinjam')
                    ->default(now())
                    ->required(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
