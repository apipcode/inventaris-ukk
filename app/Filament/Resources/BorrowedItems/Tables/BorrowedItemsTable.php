<?php

namespace App\Filament\Resources\BorrowedItems\Tables;

use App\Models\BorrowedItem;
use App\Models\ReturnedItem;
use App\Support\PdfExport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Kelas Table untuk resource BorrowedItem.
 *
 * Mendefinisikan tampilan tabel daftar peminjaman: kolom, filter, dan aksi.
 * Dipisahkan dari Resource utama agar kode lebih ringkas dan mudah dimodifikasi.
 *
 * Aksi-aksi utama:
 *  - "Kembalikan" : Membuat record pengembalian & memperbarui stok barang.
 *  - "Cetak Struk": Mengunduh PDF struk peminjaman untuk satu record.
 *  - "Export PDF" : Mengunduh PDF seluruh data tabel (dengan filter aktif).
 */
class BorrowedItemsTable
{
    /**
     * Mengkonfigurasi dan mengembalikan skema tabel.
     *
     * @param Table $table Objek tabel Filament yang akan dikonfigurasi.
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            // =========================================================
            // KOLOM TABEL
            // =========================================================
            ->columns([
                // Nama staff yang mencatat transaksi peminjaman
                TextColumn::make('staff.name')
                    ->label('Petugas')
                    ->searchable(),

                // Nama barang yang dipinjam
                TextColumn::make('item.item_name')
                    ->label('Barang')
                    ->searchable(),

                // Jumlah barang: jika sudah dikembalikan, tampilkan jumlah baik (dikurangi rusak)
                TextColumn::make('total_item')
                    ->label('Jumlah')
                    ->getStateUsing(function (BorrowedItem $record): int {
                        $damaged = (int) ($record->returnedItem?->total_damaged ?? 0);

                        // Jika sudah dikembalikan, hitung jumlah barang yang kembali dalam kondisi baik
                        if ($record->returnedItem !== null) {
                            return max(0, (int) $record->total_item - $damaged);
                        }

                        // Jika belum dikembalikan, tampilkan jumlah asli yang dipinjam
                        return (int) $record->total_item;
                    })
                    ->numeric()
                    ->sortable(),

                // Jumlah barang yang rusak saat dikembalikan (dash '-' jika belum dikembalikan)
                TextColumn::make('returnedItem.total_damaged')
                    ->label('Jumlah Rusak')
                    ->formatStateUsing(fn ($state): string => $state === null ? '-' : (string) $state)
                    ->sortable(),

                // Nama peminjam
                TextColumn::make('name_of_borrower')
                    ->label('Peminjam')
                    ->searchable(),

                // Indikator status pengembalian: ikon centang/silang berdasarkan keberadaan returnedItem
                IconColumn::make('is_returned')
                    ->label('Sudah Kembali?')
                    ->boolean()
                    ->getStateUsing(fn (BorrowedItem $record): bool => $record->returnedItem !== null),

                // Tanggal peminjaman
                TextColumn::make('date')
                    ->label('Tgl Pinjam')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                // Catatan (disembunyikan secara default, bisa ditampilkan melalui toggle kolom)
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // =========================================================
            // FILTER TABEL
            // =========================================================
            ->filters([
                // Filter berdasarkan barang — berguna saat diklik dari kolom "Dipinjam" di halaman Stok Barang
                SelectFilter::make('item_id')
                    ->relationship('item', 'item_name')
                    ->label('Filter Barang'),

                // Filter berdasarkan rentang tanggal peminjaman
                Filter::make('date_range')
                    ->label('Rentang Tanggal Pinjam')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn ($q, $date) => $q->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn ($q, $date) => $q->whereDate('date', '<=', $date),
                            );
                    }),
            ])

            // =========================================================
            // AKSI PER BARIS (Record Actions)
            // =========================================================
            ->recordActions([
                // Aksi "Kembalikan": hanya tampil jika barang BELUM dikembalikan
                Action::make('kembalikan')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-check-circle')
                    ->hidden(fn (BorrowedItem $record) => $record->returnedItem !== null)
                    ->requiresConfirmation()
                    ->form([
                        // Input jumlah barang yang rusak saat dikembalikan
                        TextInput::make('jumlah_rusak')
                            ->label('Jumlah Rusak')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(fn (BorrowedItem $record): int => (int) $record->total_item)
                            ->required(),

                        // Input catatan dari petugas penerima pengembalian
                        Textarea::make('notes')
                            ->label('Catatan Pengembalian'),
                    ])
                    ->action(function (BorrowedItem $record, array $data): void {
                        $damagedCount      = (int) ($data['jumlah_rusak'] ?? 0);
                        $goodReturnedCount = max(0, (int) $record->total_item - $damagedCount);

                        // Langkah 1: Simpan catatan pengembalian ke tabel returned_items
                        ReturnedItem::create([
                            'staff_id'         => auth()->id(),
                            'borrowed_item_id' => $record->id,
                            'return_date'      => now(),
                            'total_damaged'    => $damagedCount,
                            'notes'            => $data['notes'] ?? null,
                        ]);

                        // Langkah 2: Perbarui stok barang
                        // Barang kondisi baik kembali ke stok, barang rusak masuk ke total_repaired
                        $item = $record->item;
                        if ($item) {
                            $item->total_stock    += $goodReturnedCount;
                            $item->total_repaired += $damagedCount;
                            $item->total_borrowed  = max(0, (int) $item->total_borrowed - (int) $record->total_item);
                            $item->save();
                        }
                    }),

                // Aksi "Cetak Struk": mengunduh PDF struk satu transaksi peminjaman
                Action::make('cetakStruk')
                    ->label('Cetak Struk')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'staff'], true))
                    ->action(fn (BorrowedItem $record) => PdfExport::downloadBorrowReceipt($record)),

                // Aksi Edit: disembunyikan untuk role Staff (hanya Admin yang bisa edit data)
                EditAction::make()
                    ->hidden(fn () => auth()->user()->role === 'staff'),

                // Aksi Hapus: tersedia untuk semua peran yang memiliki akses
                DeleteAction::make(),
            ])

            // =========================================================
            // AKSI HEADER (Header Actions) — Tombol di atas tabel
            // =========================================================
            ->headerActions([
                // Tombol "Export PDF": mengunduh semua data yang sedang ditampilkan ke PDF
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($livewire) {
                        // Coba gunakan query dari Livewire agar filter aktif ikut diterapkan
                        $query = BorrowedItem::query()->with(['staff', 'item', 'returnedItem']);

                        if (is_object($livewire) && method_exists($livewire, 'getTableQueryForExport')) {
                            $query = $livewire
                                ->getTableQueryForExport()
                                ->with(['staff', 'item', 'returnedItem']);
                        } else {
                            $query->orderByDesc('date');
                        }

                        // Mapping data ke array baris untuk diteruskan ke PdfExport
                        $rows = $query
                            ->get()
                            ->map(fn (BorrowedItem $item): array => [
                                $item->staff?->name,
                                $item->item?->item_name,
                                $item->returnedItem
                                    ? max(0, (int) $item->total_item - (int) ($item->returnedItem->total_damaged ?? 0))
                                    : (int) $item->total_item,
                                $item->name_of_borrower,
                                $item->returnedItem ? 'Sudah' : 'Belum',
                                optional($item->date)?->format('d M Y H:i'),
                                $item->notes,
                            ])
                            ->all();

                        return PdfExport::download(
                            filename: 'borrowed-items.pdf',
                            title:    'Data Peminjaman Barang',
                            headers:  ['Petugas', 'Barang', 'Jumlah', 'Peminjam', 'Status', 'Tanggal Pinjam', 'Catatan'],
                            rows:     $rows,
                        );
                    }),
            ])

            // =========================================================
            // BULK ACTIONS — Aksi untuk banyak baris sekaligus
            // =========================================================
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
