<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ReturnedItem — Mewakili satu catatan pengembalian barang.
 *
 * Record ini dibuat saat tombol "Kembalikan" diklik pada tabel BorrowedItems.
 * Keberadaan record ini menjadi penanda bahwa suatu peminjaman sudah selesai.
 *
 * Side-effect pengembalian dikelola langsung di BorrowedItemsTable::configure()
 * (action 'kembalikan'), bukan di model ini, agar logika bisnis terpusat.
 */
class ReturnedItem extends Model
{
    // Kolom yang boleh diisi melalui form / mass assignment
    protected $fillable = [
        'staff_id',         // ID staff yang menerima/memproses pengembalian
        'borrowed_item_id', // ID transaksi peminjaman yang dikembalikan
        'return_date',      // Tanggal & waktu barang dikembalikan
        'total_damaged',    // Jumlah unit barang yang rusak saat dikembalikan
        'notes',            // Catatan tambahan dari petugas (opsional)
    ];

    /**
     * Relasi ke tabel users (staff yang memproses pengembalian).
     * BelongsTo: Satu catatan pengembalian diproses oleh satu staff.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Relasi ke tabel borrowed_items (data peminjaman asal).
     * BelongsTo: Satu pengembalian selalu merujuk ke satu transaksi peminjaman.
     */
    public function borrowedItem(): BelongsTo
    {
        return $this->belongsTo(BorrowedItem::class, 'borrowed_item_id');
    }
}
