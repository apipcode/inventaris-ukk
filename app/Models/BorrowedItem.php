<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BorrowedItem extends Model
{
    protected $fillable = [
        'staff_id',
        'item_id',
        'total_item',
        'name_of_borrower',
        'date',
        'notes'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Relasi ke staff (users) yang mengelola pencatatan peminjaman ini.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Relasi ke item (stock barang) yang sedang dipinjam.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemStock::class, 'item_id');
    }

    /**
     * Relasi ke data pengembalian barang. 
     * Satu peminjaman maksimal memiliki satu data pengembalian.
     */
    public function returnedItem(): HasOne
    {
        return $this->hasOne(ReturnedItem::class, 'borrowed_item_id');
    }

    /**
     * Side-effects Management: Otomatisasi update stok barang.
     */
    protected static function booted(): void
    {
        // Saat peminjaman baru dibuat
        static::created(function (BorrowedItem $record) {
            $item = $record->item;
            if ($item) {
                $item->total_stock -= $record->total_item;
                $item->total_borrowed += $record->total_item;
                $item->save();
            }
        });

        // Saat record peminjaman dihapus (bukan dikembalikan)
        static::deleting(function (BorrowedItem $record) {
            // Jika belum dikembalikan, kembalikan angkanya ke stok tersedia
            if ($record->returnedItem === null) {
                $item = $record->item;
                if ($item) {
                    $item->total_stock += $record->total_item;
                    $item->total_borrowed -= $record->total_item;
                    $item->save();
                }
            }
        });
    }
}
