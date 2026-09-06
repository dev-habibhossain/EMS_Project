<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'sku',
    'name',
    'name_bn',
    'brand_id',
    'factory_id',
    'tile_size_id',
    'pieces_per_box',
    'sqft_per_piece',
    'barcode',
    'requires_batch',
    'requires_shade',
    'default_quality_id',
    'is_active',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pieces_per_box' => 'integer',
            'sqft_per_piece' => 'decimal:6',
            'sqft_per_box' => 'decimal:6',
            'requires_batch' => 'boolean',
            'requires_shade' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return BelongsTo<Factory, $this>
     */
    public function tileFactory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }

    /**
     * @return BelongsTo<TileSize, $this>
     */
    public function tileSize(): BelongsTo
    {
        return $this->belongsTo(TileSize::class);
    }

    /**
     * @return BelongsTo<QualityGrade, $this>
     */
    public function defaultQuality(): BelongsTo
    {
        return $this->belongsTo(QualityGrade::class, 'default_quality_id');
    }

    /**
     * @return HasMany<ProductPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * @return HasMany<Shade, $this>
     */
    public function shades(): HasMany
    {
        return $this->hasMany(Shade::class);
    }

    /**
     * @return HasMany<Batch, $this>
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    /**
     * @return HasMany<WarehouseStock, $this>
     */
    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
