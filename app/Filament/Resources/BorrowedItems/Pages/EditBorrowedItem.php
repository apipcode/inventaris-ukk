<?php

namespace App\Filament\Resources\BorrowedItems\Pages;

use App\Filament\Resources\BorrowedItems\BorrowedItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBorrowedItem extends EditRecord
{
    protected static string $resource = BorrowedItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
