<?php

namespace App\Filament\Resources\BorrowedItems\Pages;

use App\Filament\Resources\BorrowedItems\BorrowedItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBorrowedItems extends ListRecords
{
    protected static string $resource = BorrowedItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
