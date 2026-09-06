<?php

namespace App\Models;

use Database\Factories\DeliveryChallanItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'challan_id',
    'product_id',
    'batch_id',
    'shade_id',
    'quality_grade_id',
    'unit_code',
    'qty_input',
    'qty_sqft',
    'pieces_per_box_snapshot',
    'sqft_per_piece_snapshot',
])]
class DeliveryChallanItem extends Model
{
    /** @use HasFactory<DeliveryChallanItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty_input' => 'decimal:4',
            'qty_sqft' => 'decimal:4',
            'pieces_per_box_snapshot' => 'integer',
            'sqft_per_piece_snapshot' => 'decimal:6',
        ];
    }

    /**
     * @return BelongsTo<DeliveryChallan, $this>
     */
    public function challan(): BelongsTo
    {
        return $this->belongsTo(DeliveryChallan::class, 'challan_id');
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
