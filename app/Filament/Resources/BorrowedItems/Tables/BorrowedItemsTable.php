<?php

namespace App\Filament\Resources\BorrowedItems\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Exports\BorrowedItemExporter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use App\Models\BorrowedItem;
use App\Models\ReturnedItem;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Table;

class BorrowedItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('staff.name')
                    ->label('Petugas')
                    ->searchable(),
                TextColumn::make('item.item_name')
                    ->label('Barang')
                    ->searchable(),
                TextColumn::make('total_item')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name_of_borrower')
                    ->label('Peminjam')
                    ->searchable(),
                // Status pengembalian: cek apakah sudah ada record returned_item
                IconColumn::make('is_returned')
                    ->label('Sudah Kembali?')
                    ->boolean()
                    ->getStateUsing(fn (BorrowedItem $record): bool => $record->returnedItem !== null),
                TextColumn::make('date')
                    ->label('Tgl Pinjam')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter untuk melihat peminjaman per barang (dipakai dari link di halaman Stok Barang)
                SelectFilter::make('item_id')
                    ->relationship('item', 'item_name')
                    ->label('Filter Barang'),
            ])
            ->recordActions([
                // Tombol "Kembalikan" hanya muncul jika barang tersebut belum dikembalikan (returnedItem kosong).
                Action::make('kembalikan')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-check-circle')
                    ->hidden(fn (BorrowedItem $record) => $record->returnedItem !== null)
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('notes')->label('Catatan Pengembalian')
                    ])
                    ->action(function (BorrowedItem $record, array $data): void {
                        // 1. Membuat catatan pengembalian di tabel returned_items.
                        ReturnedItem::create([
                            'staff_id'         => auth()->id(),
                            'borrowed_item_id' => $record->id,
                            'return_date'      => now(),
                            'notes'            => $data['notes'] ?? null,
                        ]);

                        // 2. Mengupdate stok barang: Mengembalikan barang ke 'total_stock' dan mengurangi 'total_borrowed'.
                        $item = $record->item;
                        if ($item) {
                            $item->total_stock += $record->total_item;
                            $item->total_borrowed -= $record->total_item;
                            $item->save();
                        }
                    }),
                EditAction::make()
                    ->hidden(fn () => auth()->user()->role === 'staff'),
                DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(BorrowedItemExporter::class)
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
