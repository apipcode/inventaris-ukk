<?php

namespace App\Filament\Resources\BorrowedItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use App\Models\BorrowedItem;
use App\Models\ReturnedItem;
use App\Support\PdfExport;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
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
                    ->getStateUsing(function (BorrowedItem $record): int {
                        $damaged = (int) ($record->returnedItem?->total_damaged ?? 0);

                        if ($record->returnedItem !== null) {
                            return max(0, ((int) $record->total_item - $damaged));
                        }

                        return (int) $record->total_item;
                    })
                    ->numeric()
                    ->sortable(),
                TextColumn::make('returnedItem.total_damaged')
                    ->label('Jumlah Rusak')
                    ->formatStateUsing(fn ($state): string => $state === null ? '-' : (string) $state)
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
                Filter::make('date_range')
                    ->label('Rentang Tanggal Pinjam')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn ($query, $date) => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn ($query, $date) => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                // Tombol "Kembalikan" hanya muncul jika barang tersebut belum dikembalikan (returnedItem kosong).
                Action::make('kembalikan')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-check-circle')
                    ->hidden(fn (BorrowedItem $record) => $record->returnedItem !== null)
                    ->requiresConfirmation()
                    ->form([
                        TextInput::make('jumlah_rusak')
                            ->label('Jumlah Rusak')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(fn (BorrowedItem $record): int => (int) $record->total_item)
                            ->required(),
                        Textarea::make('notes')->label('Catatan Pengembalian')
                    ])
                    ->action(function (BorrowedItem $record, array $data): void {
                        $damagedCount = (int) ($data['jumlah_rusak'] ?? 0);
                        $goodReturnedCount = max(0, (int) $record->total_item - $damagedCount);

                        // 1. Membuat catatan pengembalian di tabel returned_items.
                        ReturnedItem::create([
                            'staff_id'         => auth()->id(),
                            'borrowed_item_id' => $record->id,
                            'return_date'      => now(),
                            'total_damaged'    => $damagedCount,
                            'notes'            => $data['notes'] ?? null,
                        ]);

                        // 2. Update stok: barang baik kembali ke stok, barang rusak ditambahkan ke total_repaired.
                        $item = $record->item;
                        if ($item) {
                            $item->total_stock += $goodReturnedCount;
                            $item->total_repaired += $damagedCount;
                            $item->total_borrowed = max(0, (int) $item->total_borrowed - (int) $record->total_item);
                            $item->save();
                        }
                    }),
                Action::make('cetakStruk')
                    ->label('Cetak Struk')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'staff'], true))
                    ->action(fn (BorrowedItem $record) => PdfExport::downloadBorrowReceipt($record)),
                EditAction::make()
                    ->hidden(fn () => auth()->user()->role === 'staff'),
                DeleteAction::make(),
            ])
            ->headerActions([
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($livewire) {
                        $query = BorrowedItem::query()->with(['staff', 'item', 'returnedItem']);

                        if (is_object($livewire) && method_exists($livewire, 'getTableQueryForExport')) {
                            $query = $livewire
                                ->getTableQueryForExport()
                                ->with(['staff', 'item', 'returnedItem']);
                        } else {
                            $query->orderByDesc('date');
                        }

                        $rows = $query
                            ->get()
                            ->map(fn (BorrowedItem $borrowedItem): array => [
                                $borrowedItem->staff?->name,
                                $borrowedItem->item?->item_name,
                                $borrowedItem->returnedItem
                                    ? max(0, ((int) $borrowedItem->total_item - (int) ($borrowedItem->returnedItem->total_damaged ?? 0)))
                                    : (int) $borrowedItem->total_item,
                                $borrowedItem->name_of_borrower,
                                $borrowedItem->returnedItem ? 'Sudah' : 'Belum',
                                optional($borrowedItem->date)?->format('d M Y H:i'),
                                $borrowedItem->notes,
                            ])
                            ->all();

                        return PdfExport::download(
                            filename: 'borrowed-items.pdf',
                            title: 'Data Peminjaman Barang',
                            headers: ['Petugas', 'Barang', 'Jumlah', 'Peminjam', 'Status', 'Tanggal Pinjam', 'Catatan'],
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
