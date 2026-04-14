<?php

namespace App\Filament\Resources\ItemCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Kelas Form untuk resource ItemCategory.
 *
 * Mendefinisikan field input di halaman Create & Edit kategori barang.
 * Validasi duplikat nama (case-insensitive) ditangani di model ItemCategory::booted().
 */
class ItemCategoryForm
{
    /**
     * Mengkonfigurasi dan mengembalikan skema form kategori barang.
     *
     * @param Schema $schema Objek skema Filament yang akan dikonfigurasi.
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // Input nama kategori (contoh: "Elektronik", "Alat Tulis", "Furniture")
            TextInput::make('name')
                ->label('Nama Kategori')
                ->unique(ignoreRecord: true) // Validasi unik, kecuali record yang sedang diedit
                ->required(),

            // Input divisi/unit kerja pemilik kategori (contoh: "Bidang IT", "Sekretariat")
            TextInput::make('division')
                ->label('Divisi')
                ->required(),
        ]);
    }
}
