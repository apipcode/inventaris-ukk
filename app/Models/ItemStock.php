<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
