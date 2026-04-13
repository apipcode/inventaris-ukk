<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemCategory extends Model
{
    protected $fillable = ['name', 'division'];

    /**
     * Relasi ke item_stocks.
     * Satu kategori dapat memiliki banyak item barang yang berbeda.
     */
    public function itemStocks(): HasMany
    {
        return $this->hasMany(ItemStock::class, 'category_id');
    }
}
