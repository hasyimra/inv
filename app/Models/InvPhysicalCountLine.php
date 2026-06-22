<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvPhysicalCountLine extends Model
{
    protected $table = 'inv_physical_count_lines';

    protected $fillable = ['inv_physical_count_id', 'item_id', 'system_qty', 'counted_qty'];

    public function physicalCount(): BelongsTo
    {
        return $this->belongsTo(InvPhysicalCount::class, 'inv_physical_count_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getVarianceAttribute(): ?float
    {
        return $this->counted_qty === null ? null : $this->counted_qty - $this->system_qty;
    }
}
