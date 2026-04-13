<?php

namespace App\Filament\Exports;

use App\Models\ItemStock;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ItemStockExporter extends Exporter
{
    protected static ?string $model = ItemStock::class;

    public static function getColumns(): array
    {
        // Daftar kolom stok barang yang akan diekspor, label sudah disesuaikan ke Bahasa Indonesia.
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('category.name')->label('Kategori'),
            ExportColumn::make('item_name')->label('Nama Barang'),
            ExportColumn::make('total_stock')->label('Stok'),
            ExportColumn::make('total_repaired')->label('Rusak'),
            ExportColumn::make('total_borrowed')->label('Dipinjam'),
            ExportColumn::make('created_at')->label('Dibuat'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor stok barang telah selesai dan ' . Number::format($export->successful_rows) . ' ' . str('baris')->plural($export->successful_rows) . ' berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('baris')->plural($failedRowsCount) . ' gagal diekspor.';
        }

        return $body;
    }
}
