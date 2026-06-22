<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvAdjustmentLine extends Model
{
    protected $table = 'inv_adjustment_lines';

    protected $fillable = ['inv_adjustment_id', 'item_id', 'qty_adjusted', 'notes'];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(InvAdjustment::class, 'inv_adjustment_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
