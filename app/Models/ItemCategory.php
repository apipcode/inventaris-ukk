<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

/**
 * Model ItemCategory — Mewakili kategori/kelompok barang inventaris.
 *
 * Setiap kategori memiliki nama dan divisi (unit kerja yang memiliki barang).
 * Contoh: Kategori "Elektronik" milik Divisi IT.
 *
 * Validasi: Nama kategori bersifat unik (tidak boleh duplikat, case-insensitive).
 */
class ItemCategory extends Model
{
    // Kolom yang boleh diisi melalui form / mass assignment
    protected $fillable = ['name', 'division'];

    /**
     * Relasi ke tabel item_stocks.
     * Satu kategori dapat memiliki banyak data barang (stok) yang berbeda.
     */
    public function itemStocks(): HasMany
    {
        return $this->hasMany(ItemStock::class, 'category_id');
    }

    /**
     * Hook yang berjalan sebelum data disimpan (create & update).
     * Mencegah duplikasi nama kategori secara case-insensitive menggunakan LOWER().
     */
    protected static function booted(): void
    {
        static::saving(function (ItemCategory $itemCategory): void {
            // Normalisasi nama: hilangkan spasi di tepi & ubah ke huruf kecil untuk perbandingan
            $normalizedName = mb_strtolower(trim((string) $itemCategory->name));

            // Cek apakah nama yang sama sudah ada di database (kecuali record itu sendiri saat update)
            $duplicateExists = static::query()
                ->whereRaw('LOWER(name) = ?', [$normalizedName])
                ->when($itemCategory->exists, fn ($query) => $query->whereKeyNot($itemCategory->getKey()))
                ->exists();

            if ($duplicateExists) {
                throw ValidationException::withMessages([
                    'name' => 'Nama kategori sudah digunakan. Gunakan nama lain agar tidak duplikasi.',
                ]);
            }
        });
    }
}
