<?php

namespace App\Filament\Resources\ItemStocks\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Exports\ItemStockExporter;
use Filament\Forms\Components\TextInput;
use App\Models\ItemStock;
use App\Filament\Resources\BorrowedItems\BorrowedItemResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('item_name')
                    ->label('Nama Barang')
                    ->searchable(),
                TextColumn::make('total_stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_repaired')
                    ->label('Rusak')
                    ->numeric()
                    ->sortable(),
                // Jumlah dipinjam bisa diklik untuk melihat detail peminjaman barang terkait.
                // Fungsi url() akan mengarahkan user ke halaman Peminjaman dengan filter barang yang dipilih.
                TextColumn::make('total_borrowed')
                    ->label('Dipinjam')
                    ->numeric()
                    ->sortable()
                    ->url(fn (ItemStock $record): string => BorrowedItemResource::getUrl('index') . '?tableFilters[item_id][value]=' . $record->id),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // Aksi kustom untuk menambah jumlah barang rusak
                Action::make('tambahRusak')
                    ->label('Tambah Rusak')
                    ->icon('heroicon-o-wrench')
                    ->form([
                        TextInput::make('jumlah_rusak')
                            ->label('Jumlah Rusak Tambahan')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (ItemStock $record) => $record->total_stock)
                            ->required()
                    ])
                    ->action(function (ItemStock $record, array $data): void {
                        // Logika bisinis: Menambah jumlah rusak dan secara otomatis mengurangi stok yang tersedia.
                        $record->total_repaired += $data['jumlah_rusak'];
                        if ($record->total_stock >= $data['jumlah_rusak']) {
                            $record->total_stock -= $data['jumlah_rusak'];
                        }
                        $record->save();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(ItemStockExporter::class)
                    ->formats([ExportFormat::Csv])
                    ->label('Export CSV'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
