<?php

namespace App\Filament\Resources\ItemStocks\Tables;

use App\Filament\Resources\BorrowedItems\BorrowedItemResource;
use App\Models\ItemStock;
use App\Support\PdfExport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Kelas Table untuk resource ItemStock.
 *
 * Mendefinisikan tampilan tabel daftar stok barang: kolom, dan aksi.
 * Dipisahkan dari Resource utama agar kode lebih ringkas dan mudah dimodifikasi.
 *
 * Fitur khusus:
 *  - Kolom "Dipinjam" dapat diklik untuk melihat riwayat peminjaman barang tersebut.
 *  - Aksi "Tambah Rusak" memungkinkan admin menambah jumlah barang rusak secara manual.
 */
class ItemStocksTable
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
                // Nama kategori barang (dari relasi)
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable(),

                // Nama barang inventaris
                TextColumn::make('item_name')
                    ->label('Nama Barang')
                    ->searchable(),

                // Jumlah stok yang saat ini tersedia untuk dipinjam
                TextColumn::make('total_stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable(),

                // Total barang yang rusak (akumulasi dari semua pengembalian)
                TextColumn::make('total_repaired')
                    ->label('Rusak')
                    ->numeric()
                    ->sortable(),

                // Jumlah barang yang sedang dipinjam (klik untuk melihat riwayat peminjaman)
                TextColumn::make('total_borrowed')
                    ->label('Dipinjam')
                    ->numeric()
                    ->sortable()
                    // URL yang mengarahkan ke halaman Peminjaman dengan filter barang ini aktif
                    ->url(fn (ItemStock $record): string =>
                        BorrowedItemResource::getUrl('index') . '?tableFilters[item_id][value]=' . $record->id
                    ),

                // Tanggal barang pertama kali ditambahkan (disembunyikan secara default)
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // =========================================================
            // AKSI PER BARIS (Record Actions)
            // =========================================================
            ->recordActions([
                // Aksi "Tambah Rusak": mengurangi stok & menambah total_repaired secara manual
                Action::make('tambahRusak')
                    ->label('Tambah Rusak')
                    ->icon('heroicon-o-wrench')
                    ->form([
                        TextInput::make('jumlah_rusak')
                            ->label('Jumlah Rusak Tambahan')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->action(function (ItemStock $record, array $data): void {
                        // Tambahkan ke total rusak
                        $record->total_repaired += $data['jumlah_rusak'];

                        // Kurangi dari stok jika stok mencukupi (mencegah nilai negatif)
                        if ($record->total_stock >= $data['jumlah_rusak']) {
                            $record->total_stock -= $data['jumlah_rusak'];
                        }

                        $record->save();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])

            // =========================================================
            // AKSI HEADER (Header Actions) — Tombol di atas tabel
            // =========================================================
            ->headerActions([
                // Tombol "Export PDF": mengunduh seluruh data stok barang ke PDF
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
                            title:    'Data Stok Barang',
                            headers:  ['Kategori', 'Nama Barang', 'Stok', 'Rusak', 'Dipinjam', 'Dibuat'],
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