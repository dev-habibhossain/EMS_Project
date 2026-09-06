<?php

namespace App\Models;

use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'warehouse_stock_id',
    'product_id',
    'warehouse_id',
    'batch_id',
    'shade_id',
    'quality_grade_id',
    'condition',
    'direction',
    'qty_sqft',
    'qty_input',
    'unit_code',
    'pieces_per_box_snapshot',
    'sqft_per_piece_snapshot',
    'movement_type',
    'document_type',
    'document_id',
    'note',
    'occurred_at',
    'created_by',
])]
class StockMovement extends Model
{
    /** @use HasFactory<StockMovementFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty_sqft' => 'decimal:4',
            'qty_input' => 'decimal:4',
            'pieces_per_box_snapshot' => 'integer',
            'sqft_per_piece_snapshot' => 'decimal:6',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WarehouseStock, $this>
     */
    public function warehouseStock(): BelongsTo
    {
        return $this->belongsTo(WarehouseStock::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
