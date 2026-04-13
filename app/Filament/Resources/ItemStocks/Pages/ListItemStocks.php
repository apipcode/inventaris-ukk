<?php

namespace App\Filament\Resources\ItemStocks\Pages;

use App\Filament\Resources\ItemStocks\ItemStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListItemStocks extends ListRecords
{
    protected static string $resource = ItemStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
