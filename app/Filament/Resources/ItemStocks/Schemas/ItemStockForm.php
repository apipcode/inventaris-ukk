<?php

namespace App\Filament\Resources\ItemStocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Kelas Form untuk resource ItemStock.
 *
 * Mendefinisikan field input di halaman Create & Edit data barang.
 * Kolom 'total_borrowed' bersifat read-only karena dikelola otomatis oleh sistem
 * melalui model hooks di BorrowedItem::booted().
 */
class ItemStockForm
{
    /**
     * Mengkonfigurasi dan mengembalikan skema form data barang.
     *
     * @param Schema $schema Objek skema Filament yang akan dikonfigurasi.
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // Dropdown pilih kategori barang (relasi ke tabel item_categories)
            Select::make('category_id')
                ->relationship('category', 'name')
                ->label('Kategori')
                ->searchable()
                ->required(),

            // Input nama barang (validasi duplikat per kategori ada di model ItemStock::booted())
            TextInput::make('item_name')
                ->label('Nama Barang')
                ->required(),

            // Input jumlah stok awal barang yang tersedia
            TextInput::make('total_stock')
                ->label('Jumlah Stok')
                ->required()
                ->numeric()
                ->default(0),

            // Input jumlah barang rusak (bisa diisi manual saat awal, setelah itu dikelola sistem)
            TextInput::make('total_repaired')
                ->label('Jumlah Rusak')
                ->required()
                ->numeric()
                ->default(0),

            // Field "Jumlah Dipinjam" bersifat READ-ONLY (disabled).
            // Nilai ini diperbarui SECARA OTOMATIS oleh sistem saat terjadi transaksi
            // peminjaman (BorrowedItem::created) atau pengembalian (aksi "Kembalikan").
            // dehydrated(true) = nilainya tetap terkirim ke server meski field-nya disabled.
            TextInput::make('total_borrowed')
                ->label('Jumlah Dipinjam')
                ->numeric()
                ->default(0)
                ->disabled()
                ->dehydrated(true),
        ]);
    }
}
