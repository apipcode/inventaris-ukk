<?php

namespace App\Filament\Resources\ItemCategories\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use App\Models\ItemCategory;
use App\Support\PdfExport;

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
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $rows = ItemCategory::query()
                            ->withCount('itemStocks')
                            ->orderBy('name')
                            ->get()
                            ->map(fn (ItemCategory $category): array => [
                                $category->name,
                                $category->division,
                                $category->item_stocks_count,
                                optional($category->created_at)?->format('d M Y'),
                            ])
                            ->all();

                        return PdfExport::download(
                            filename: 'item-categories.pdf',
                            title: 'Data Kategori Barang',
                            headers: ['Nama Kategori', 'Divisi', 'Jumlah Barang', 'Dibuat'],
                            rows: $rows,
                        );
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}