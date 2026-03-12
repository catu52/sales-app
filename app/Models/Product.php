<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Item;

class Product extends Item
{
    use HasFactory;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'products';

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
    * The attributes that are mass assignable.
    *
    * @var array<int>
    */
    protected $fillable = ['item_id', 'stock_quantity'];

    /**
     * Boot method to handle model events, such as cascading deletes.
     * When a Product is deleted, we want to ensure the associated Item is also deleted to maintain data integrity.
     * This is crucial because the Item contains the shared attributes and if a Product is removed, the Item should not remain orphaned in the database.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically join the items table to get parent attributes
        static::addGlobalScope('withItem', function (Builder $builder) {
            $builder->join('items', 'products.item_id', '=', 'items.id')
                    ->select('products.*', 'items.name', 'items.price', 'items.is_available', 'items.item_type');
        });

        static::deleting(function ($product) {
            // Ensure the associated Item is also deleted
            $product->item()->delete();
        });
    }

    /**
     * Relationship: Parent Item details.
     * Each Product belongs to one Item, which contains the shared attributes.
     * This allows us to access the common properties like name, base_price, and availability
     * while keeping product-specific details (like stock_quantity) in this separate table.
     * 
     * @return BelongsTo
     */
    public function item(): BelongsTo {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
