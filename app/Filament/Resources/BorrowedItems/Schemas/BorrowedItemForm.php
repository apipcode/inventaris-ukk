<?php

namespace App\Filament\Resources\BorrowedItems\Schemas;

use App\Models\ItemStock;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                    ->relationship(
                        name: 'item',
                        titleAttribute: 'item_name',
                        modifyQueryUsing: fn (Builder $query): Builder => (auth()->check() && auth()->user()->role === 'staff')
                            ? $query->where('total_stock', '>', 0)
                            : $query,
                    )
                    ->label('Barang')
                    ->getOptionLabelFromRecordUsing(fn (ItemStock $record): string => "{$record->item_name} (Stok: {$record->total_stock})")
                    ->searchable()
                    ->preload()
                    ->live()
                    ->helperText('Untuk staff, daftar ini hanya menampilkan barang yang stoknya masih tersedia.')
                    ->required(),
                TextInput::make('total_item')
                    ->label('Jumlah Pinjam')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->rule(function (callable $get) {
                        return function (string $attribute, $value, \Closure $fail) use ($get): void {
                            $itemId = $get('item_id');
                            $stock = $itemId ? (\App\Models\ItemStock::find($itemId)?->total_stock ?? 0) : 0;

                            if ((int) $value > (int) $stock) {
                                $fail("Jumlah pinjam tidak boleh melebihi stok tersedia ({$stock}).");
                            }
                        };
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
