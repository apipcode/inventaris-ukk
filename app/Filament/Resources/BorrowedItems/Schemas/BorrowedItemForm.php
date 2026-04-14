<?php

namespace App\Filament\Resources\BorrowedItems\Schemas;

use App\Models\ItemStock;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

/**
 * Kelas Form untuk resource BorrowedItem.
 *
 * Mendefinisikan semua field input yang muncul di halaman Create & Edit peminjaman.
 * Dipisahkan dari Resource utama agar kode lebih bersih dan mudah dimodifikasi.
 */
class BorrowedItemForm
{
    /**
     * Mengkonfigurasi dan mengembalikan skema form.
     *
     * @param Schema $schema Objek skema Filament yang akan dikonfigurasi.
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // Field tersembunyi: diisi otomatis dengan ID user yang sedang login (staff pencatat)
            Hidden::make('staff_id')
                ->default(fn () => auth()->id()),

            // Dropdown untuk memilih barang yang akan dipinjam
            // Jika user adalah Staff, dropdown hanya menampilkan barang dengan stok > 0
            Select::make('item_id')
                ->relationship(
                    name: 'item',
                    titleAttribute: 'item_name',
                    modifyQueryUsing: fn (Builder $query): Builder =>
                        (auth()->check() && auth()->user()->role === 'staff')
                            ? $query->where('total_stock', '>', 0)  // Filter stok untuk staff
                            : $query,                                // Admin melihat semua barang
                )
                ->label('Barang')
                ->getOptionLabelFromRecordUsing(
                    // Tampilkan nama barang beserta info stok saat ini
                    fn (ItemStock $record): string => "{$record->item_name} (Stok: {$record->total_stock})"
                )
                ->searchable()
                ->preload()
                ->live() // Memperbarui field lain (jumlah stok) secara reaktif saat dipilih
                ->helperText('Untuk staff, daftar ini hanya menampilkan barang yang stoknya masih tersedia.')
                ->required(),

            // Input jumlah barang yang dipinjam, dengan validasi tidak melebihi stok
            TextInput::make('total_item')
                ->label('Jumlah Pinjam')
                ->required()
                ->numeric()
                ->minValue(1)
                // Validasi kustom: pastikan jumlah pinjam tidak melebihi stok yang tersedia
                ->rule(function (callable $get) {
                    return function (string $attribute, $value, \Closure $fail) use ($get): void {
                        $itemId = $get('item_id');
                        $stock  = $itemId ? (ItemStock::find($itemId)?->total_stock ?? 0) : 0;

                        if ((int) $value > (int) $stock) {
                            $fail("Jumlah pinjam tidak boleh melebihi stok tersedia ({$stock}).");
                        }
                    };
                })
                // Teks bantuan reaktif: menampilkan stok tersedia setelah barang dipilih
                ->helperText(function (callable $get) {
                    $itemId = $get('item_id');
                    if (! $itemId) return null;

                    $stock = ItemStock::find($itemId)?->total_stock ?? 0;
                    return "Stok tersedia: {$stock}";
                }),

            // Input nama orang yang meminjam barang
            TextInput::make('name_of_borrower')
                ->label('Nama Peminjam')
                ->required(),

            // Input tanggal & waktu peminjaman, default ke waktu sekarang
            DateTimePicker::make('date')
                ->label('Tanggal Pinjam')
                ->default(now())
                ->required(),

            // Input catatan tambahan (opsional), ditampilkan penuh selebar kolom
            Textarea::make('notes')
                ->label('Catatan')
                ->columnSpanFull(),
        ]);
    }
}
