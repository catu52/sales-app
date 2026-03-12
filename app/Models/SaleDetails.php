<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetails extends Model
{
    /**
     * Mass assignable attributes.
     * Note: 'unit_price' is stored at the time of sale to preserve historical pricing
     * 
     * @var array<int>
     */
    protected $fillable = ['sale_id', 'item_id', 'quantity', 'unit_price'];

    /**
     * Relationship: The sale this detail belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item(): BelongsTo {
        return $this->belongsTo(\App\Models\Item::class);
    }
}
