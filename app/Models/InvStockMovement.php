<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvStockMovement extends Model
{
    protected $table = 'inv_stock_movements';

    protected $fillable = [
        'item_id', 'warehouse_id', 'qty', 'type', 'unit_cost',
        'source_type', 'source_id', 'moved_at',
    ];

    protected function casts(): array
    {
        return [
            'moved_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public static function typeOptions(): array
    {
        return [
            'receipt' => 'Penerimaan (PO)',
            'sale' => 'Penjualan',
            'adjustment' => 'Adjustment / Stock Opname',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[$this->type] ?? $this->type;
    }
}
