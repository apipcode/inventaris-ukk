<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnedItem extends Model
{
    protected $fillable = [
        'staff_id',
        'borrowed_item_id',
        'return_date',
        'total_damaged',
        'notes'
    ];

    /**
     * Relasi ke staff (users) yang mendaftarkan atau menerima pengembalian ini.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Relasi balik ke data peminjaman terkait.
     * Satu data pengembalian merujuk pada satu data peminjaman yang spesifik.
     */
    public function borrowedItem(): BelongsTo
    {
        return $this->belongsTo(BorrowedItem::class, 'borrowed_item_id');
    }
}
