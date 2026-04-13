<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class ItemStock extends Model
{
    protected $fillable = [
        'category_id', 
        'item_name', 
        'total_stock', 
        'total_repaired', 
        'total_borrowed'
    ];

    /**
     * Relasi balik ke kategori item.
     * Setiap item barang terdaftar di bawah satu kategori tertentu.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    /**
     * Relasi ke pencatatan peminjaman item.
     * Satu item barang bisa dicatat dalam banyak baris transaksi peminjaman (riwayat).
     */
    public function borrowedItems(): HasMany
    {
        return $this->hasMany(BorrowedItem::class, 'item_id');
    }

    protected static function booted(): void
    {
        static::saving(function (ItemStock $itemStock): void {
            $normalizedName = mb_strtolower(trim((string) $itemStock->item_name));

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
