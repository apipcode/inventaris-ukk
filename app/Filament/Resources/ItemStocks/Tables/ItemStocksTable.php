<?php

namespace App\Filament\Resources\ItemStocks\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Forms\Components\TextInput;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use App\Models\ItemStock;
use App\Filament\Resources\BorrowedItems\BorrowedItemResource;
use App\Support\PdfExport;

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

                TextColumn::make('total_borrowed')
                    ->label('Dipinjam')
                    ->numeric()
                    ->sortable()
                    ->url(fn (ItemStock $record): string => 
                        BorrowedItemResource::getUrl('index') . '?tableFilters[item_id][value]=' . $record->id
                    ),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('tambahRusak')
                    ->label('Tambah Rusak')
                    ->icon('heroicon-o-wrench')
                    ->form([
                        TextInput::make('jumlah_rusak')
                            ->label('Jumlah Rusak Tambahan')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                    ])
                    ->action(function (ItemStock $record, array $data): void {
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
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $rows = ItemStock::query()
                            ->with('category')
                            ->orderBy('item_name')
                            ->get()
                            ->map(fn (ItemStock $stock): array => [
                                $stock->category?->name,
                                $stock->item_name,
                                $stock->total_stock,
                                $stock->total_repaired,
                                $stock->total_borrowed,
                                optional($stock->created_at)?->format('d M Y'),
                            ])
                            ->all();

                        return PdfExport::download(
                            filename: 'item-stocks.pdf',
                            title: 'Data Stok Barang',
                            headers: ['Kategori', 'Nama Barang', 'Stok', 'Rusak', 'Dipinjam', 'Dibuat'],
                            rows: $rows,
                        );
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}