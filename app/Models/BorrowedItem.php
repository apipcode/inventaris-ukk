<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model BorrowedItem — Mewakili satu transaksi peminjaman barang.
 *
 * Setiap record di tabel ini adalah satu kejadian peminjaman:
 * siapa yang meminjam, barang apa, berapa jumlah, dan kapan.
 *
 * Model ini memiliki side-effect otomatis:
 *  - Saat dibuat  : total_stock barang berkurang, total_borrowed bertambah.
 *  - Saat dihapus : jika belum dikembalikan, stok dikembalikan ke kondisi semula.
 */
class BorrowedItem extends Model
{
    // Kolom yang boleh diisi melalui form / mass assignment
    protected $fillable = [
        'staff_id',       // ID staff yang mencatat transaksi peminjaman
        'item_id',        // ID barang yang dipinjam
        'total_item',     // Jumlah unit barang yang dipinjam
        'name_of_borrower', // Nama lengkap orang yang meminjam barang
        'date',           // Tanggal & waktu peminjaman
        'notes',          // Catatan tambahan (opsional)
    ];

    // Casting kolom 'date' ke tipe Carbon (objek tanggal/waktu)
    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Relasi ke tabel users (sebagai staff pencatat).
     * BelongsTo: Satu catatan peminjaman dimiliki oleh satu staff.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Relasi ke tabel item_stocks (barang yang dipinjam).
     * BelongsTo: Setiap transaksi merujuk pada satu jenis barang.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemStock::class, 'item_id');
    }

    /**
     * Relasi ke tabel returned_items (data pengembalian).
     * HasOne: Satu peminjaman hanya bisa memiliki satu catatan pengembalian.
     * Jika null, berarti barang belum dikembalikan.
     */
    public function returnedItem(): HasOne
    {
        return $this->hasOne(ReturnedItem::class, 'borrowed_item_id');
    }

    /**
     * Mendaftarkan event listener (Model Hooks) untuk manajemen stok otomatis.
     */
    protected static function booted(): void
    {
        // EVENT: Setelah transaksi peminjaman baru berhasil dibuat
        static::created(function (BorrowedItem $record) {
            $item = $record->item;
            if ($item) {
                // Kurangi stok tersedia dan tambah jumlah yang sedang dipinjam
                $item->total_stock   -= $record->total_item;
                $item->total_borrowed += $record->total_item;
                $item->save();
            }
        });

        // EVENT: Sebelum record peminjaman dihapus secara permanen
        static::deleting(function (BorrowedItem $record) {
            // Hanya kembalikan stok jika barang BELUM dikembalikan (returnedItem null)
            // Jika sudah dikembalikan, stok sudah diurus oleh aksi "Kembalikan"
            if ($record->returnedItem === null) {
                $item = $record->item;
                if ($item) {
                    $item->total_stock    += $record->total_item;
                    $item->total_borrowed -= $record->total_item;
                    $item->save();
                }
            }
        });
    }
}
