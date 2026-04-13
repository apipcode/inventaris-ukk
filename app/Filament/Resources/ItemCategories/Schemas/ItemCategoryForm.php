<?php

namespace App\Filament\Resources\ItemCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ItemCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Field untuk menginput nama kategori (misal: Elektronik, ATK, dll)
                TextInput::make('name')
                    ->label('Nama Kategori')
                    ->unique(ignoreRecord: true)
                    ->required(),
                    
                // Field untuk menginput divisi pemilik barang (misal: Bidang IT, Sekretariat, dll)
                TextInput::make('division')
                    ->label('Divisi')
                    ->required(),
            ]);
    }
}
