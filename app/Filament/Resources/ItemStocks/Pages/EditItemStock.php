<?php

namespace App\Filament\Resources\ItemStocks\Pages;

use App\Filament\Resources\ItemStocks\ItemStockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditItemStock extends EditRecord
{
    protected static string $resource = ItemStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
