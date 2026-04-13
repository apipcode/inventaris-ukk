<?php

namespace App\Filament\Exports;

use App\Models\BorrowedItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class BorrowedItemExporter extends Exporter
{
    protected static ?string $model = BorrowedItem::class;

    public static function getColumns(): array
    {
        // Daftar kolom transaksi peminjaman yang akan diekspor.
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('staff.name')->label('Petugas'),
            ExportColumn::make('item.item_name')->label('Nama Barang'),
            ExportColumn::make('total_item')->label('Jumlah'),
            ExportColumn::make('name_of_borrower')->label('Peminjam'),
            ExportColumn::make('date')->label('Tanggal Pinjam'),
            ExportColumn::make('notes')->label('Catatan'),
            ExportColumn::make('created_at')->label('Dibuat'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor data peminjaman telah selesai dan ' . Number::format($export->successful_rows) . ' ' . str('baris')->plural($export->successful_rows) . ' berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('baris')->plural($failedRowsCount) . ' gagal diekspor.';
        }

        return $body;
    }
}
