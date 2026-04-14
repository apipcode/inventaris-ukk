<?php

namespace App\Filament\Resources\ItemCategories\Tables;

use App\Models\ItemCategory;
use App\Support\PdfExport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Kelas Table untuk resource ItemCategory.
 *
 * Mendefinisikan tampilan tabel daftar kategori barang: kolom, dan aksi.
 * Dipisahkan dari Resource utama agar kode lebih ringkas dan mudah dimodifikasi.
 */
class ItemCategoriesTable
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
                // Nama kategori barang
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable(),

                // Divisi/unit kerja pemilik kategori
                TextColumn::make('division')
                    ->label('Divisi')
                    ->searchable(),

                // Jumlah barang (item_stocks) yang terdaftar di bawah kategori ini
                TextColumn::make('item_stocks_count')
                    ->counts('itemStocks')
                    ->label('Jumlah Barang')
                    ->sortable(),

                // Tanggal kategori dibuat (disembunyikan secara default)
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
                EditAction::make(),
                DeleteAction::make(),
            ])

            // =========================================================
            // AKSI HEADER (Header Actions) — Tombol di atas tabel
            // =========================================================
            ->headerActions([
                // Tombol "Export PDF": mengunduh seluruh data kategori barang ke PDF
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $rows = ItemCategory::query()
                            ->withCount('itemStocks')
                            ->orderBy('name')
                            ->get()
                            ->map(fn (ItemCategory $category): array => [
                                $category->name,
                                $category->division,
                                $category->item_stocks_count,
                                optional($category->created_at)?->format('d M Y'),
                            ])
                            ->all();

                        return PdfExport::download(
                            filename: 'item-categories.pdf',
                            title:    'Data Kategori Barang',
                            headers:  ['Nama Kategori', 'Divisi', 'Jumlah Barang', 'Dibuat'],
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