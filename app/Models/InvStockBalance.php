<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvStockBalance extends Model
{
    protected $table = 'inv_stock_balances';

    protected $fillable = ['item_id', 'warehouse_id', 'qty_on_hand', 'unit_cost'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
