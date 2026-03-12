<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Item;

class Service extends Item
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'services';

     /**
     * The primary key associated with the table.
     *
     * @var string
     */

    protected $primaryKey = 'item_id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Mass assignable attributes for the Service model.
     * 
     * @var array<int>
     */
    protected $fillable = ['item_id', 'required_product_id'];

    /**
     * Boot method to handle model events, such as cascading deletes.
     * When a Service is deleted, we want to ensure the associated Item is also deleted to maintain data integrity.
     * This is crucial because the Item contains the shared attributes and if a Service is removed, the Item should not remain orphaned in the database.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically join the items table to get parent attributes
        static::addGlobalScope('withItem', function (Builder $builder) {
            $builder->join('items', 'services.item_id', '=', 'items.id')
                    ->select('services.*', 'items.name', 'items.price', 'items.is_available', 'items.item_type');
        });

        static::deleting(function ($service) {
            // Ensure the associated Item is also deleted
            $service->item()->delete();
        });
    }

    /**
     * Relationship: Parent Item details.
     * Each Service belongs to one Item, which contains the shared attributes.
     * This allows us to access the common properties like name, price, and availability
     * while keeping service-specific details (like required_product_id) in this separate table.
     * 
     * @return BelongsTo
     */
    public function item(): BelongsTo {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Relationship: Required Product details.
     * Each Service may depend on one Product, which contains the shared attributes.
     * This allows us to access the common properties like name, price, and availability
     * while keeping service-specific details (like required_product_id) in this separate table.
     * 
     * @return BelongsTo
     */
    public function requiredProduct(): BelongsTo {
        return $this->belongsTo(Item::class, 'required_product_id');
    }
}
