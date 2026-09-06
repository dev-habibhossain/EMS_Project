<?php

namespace App\Models;

use Database\Factories\TileSizeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['label', 'length_mm', 'width_mm', 'default_sqft_per_piece'])]
class TileSize extends Model
{
    /** @use HasFactory<TileSizeFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'length_mm' => 'integer',
            'width_mm' => 'integer',
            'default_sqft_per_piece' => 'decimal:6',
        ];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
