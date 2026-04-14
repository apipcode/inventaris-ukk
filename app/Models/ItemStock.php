<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

/**
 * Model ItemStock — Mewakili satu jenis barang beserta jumlah stoknya.
 *
 * Kolom-kolom stok dikelola secara otomatis oleh sistem:
 *  - total_stock    : Jumlah barang yang tersedia untuk dipinjam.
 *  - total_borrowed : Jumlah yang sedang dalam status dipinjam (otomatis berubah).
 *  - total_repaired : Jumlah akumulasi barang yang rusak saat dikembalikan.
 *
 * Validasi: Nama barang bersifat unik di dalam satu kategori yang sama.
 */
class ItemStock extends Model
{
    // Kolom yang boleh diisi melalui form / mass assignment
    protected $fillable = [
        'category_id',
        'item_name',
        'total_stock',
        'total_repaired',
        'total_borrowed',
    ];

    /**
     * Relasi ke tabel item_categories.
     * Setiap barang terdaftar di bawah satu kategori tertentu.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    /**
     * Relasi ke tabel borrowed_items (riwayat peminjaman).
     * Satu barang bisa memiliki banyak catatan peminjaman (histori).
     */
    public function borrowedItems(): HasMany
    {
        return $this->hasMany(BorrowedItem::class, 'item_id');
    }

    /**
     * Hook yang berjalan sebelum data disimpan (create & update).
     * Mencegah duplikasi nama barang dalam satu kategori yang sama (case-insensitive).
     */
    protected static function booted(): void
    {
        static::saving(function (ItemStock $itemStock): void {
            // Normalisasi nama barang: hilangkan spasi di tepi & ubah ke huruf kecil
            $normalizedName = mb_strtolower(trim((string) $itemStock->item_name));

            // Cek duplikat di kategori yang sama (kecuali record itu sendiri saat update)
            $duplicateExists = static::query()
                ->where('category_id', $itemStock->category_id)
                ->whereRaw('LOWER(item_name) = ?', [$normalizedName])
                ->when($itemStock->exists, fn ($query) => $query->whereKeyNot($itemStock->getKey()))
                ->exists();

            if ($duplicateExists) {
                throw ValidationException::withMessages([
                    'item_name' => 'Nama barang sudah ada di kategori ini. Gunakan nama lain agar tidak duplikasi.',
                ]);
            }
        });
    }
}
