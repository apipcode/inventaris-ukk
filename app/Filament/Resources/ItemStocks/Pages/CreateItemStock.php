<?php

namespace App\Filament\Resources\ItemStocks\Pages;

use App\Filament\Resources\ItemStocks\ItemStockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateItemStock extends CreateRecord
{
    protected static string $resource = ItemStockResource::class;
}
