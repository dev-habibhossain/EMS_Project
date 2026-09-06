<?php

namespace App\Models;

use Database\Factories\SalesReturnItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sales_return_id',
    'sale_item_id',
    'product_id',
    'batch_id',
    'shade_id',
    'quality_grade_id',
    'condition',
    'unit_code',
    'qty_input',
    'qty_sqft',
    'unit_price',
    'line_total',
    'pieces_per_box_snapshot',
    'sqft_per_piece_snapshot',
])]
class SalesReturnItem extends Model
{
    /** @use HasFactory<SalesReturnItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty_input' => 'decimal:4',
            'qty_sqft' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'pieces_per_box_snapshot' => 'integer',
            'sqft_per_piece_snapshot' => 'decimal:6',
        ];
    }

    /**
     * @return BelongsTo<SalesReturn, $this>
     */
    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class);
    }

    /**
     * @return BelongsTo<SaleItem, $this>
     */
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Batch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * @return BelongsTo<Shade, $this>
     */
    public function shade(): BelongsTo
    {
        return $this->belongsTo(Shade::class);
    }

    /**
     * @return BelongsTo<QualityGrade, $this>
     */
    public function qualityGrade(): BelongsTo
    {
        return $this->belongsTo(QualityGrade::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_code', 'code');
    }
}
