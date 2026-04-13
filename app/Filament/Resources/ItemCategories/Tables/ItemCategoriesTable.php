<?php

namespace App\Filament\Resources\ItemCategories\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Exports\ItemCategoryExporter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable(),
                TextColumn::make('division')
                    ->label('Divisi')
                    ->searchable(),
                // Menampilkan jumlah total item barang yang terdaftar di bawah kategori ini.
                // Menggunakan fungsi counts('itemStocks') yang merujuk pada relasi di Model ItemCategory.
                TextColumn::make('item_stocks_count')
                    ->counts('itemStocks')
                    ->label('Jumlah Barang')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(ItemCategoryExporter::class)
                    ->formats([ExportFormat::Csv])
                    ->label('Export CSV'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
