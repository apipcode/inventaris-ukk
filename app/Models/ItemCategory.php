<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

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

    protected static function booted(): void
    {
        static::saving(function (ItemCategory $itemCategory): void {
            $normalizedName = mb_strtolower(trim((string) $itemCategory->name));

            $duplicateExists = static::query()
                ->whereRaw('LOWER(name) = ?', [$normalizedName])
                ->when($itemCategory->exists, fn ($query) => $query->whereKeyNot($itemCategory->getKey()))
                ->exists();

            if ($duplicateExists) {
                throw ValidationException::withMessages([
                    'name' => 'Nama kategori sudah digunakan. Gunakan nama lain agar tidak duplikasi.',
                ]);
            }
        });
    }
}
