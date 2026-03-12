<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\ItemType;
use App\Models\Product;
use App\Models\Service;

/**
 * UML: Item <<Abstract>>
 */
abstract class Item extends Model
{
    /**
     * The table associated with the model.
     * All children (Product, Service) share this base table.
     */
    protected $table = 'items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'price',
        'is_available',
        'item_type' // Discriminator: 'product' or 'service'
    ];

    /**
     * Relationship: Child Product details.
     * Only populated if type is 'product'.
     */
    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'item_id');
    }

    /**
     * Relationship: Child Service details.
     * Only populated if type is 'service'.
     */
    public function service(): HasOne
    {
        return $this->hasOne(Service::class, 'item_id');
    }

    /**
     * Logic to determine availability based on type and dependencies.
     */
    public function isCurrentlyAvailable(): bool
    {
        // Rule: General availability flag must be true
        if (!$this->is_available) {
            return false;
        }

        // Rule: Product must have stock
        if ($this->item_type === ItemType::PRODUCT) {
            return $this->product && $this->product->stock_quantity > 0;
        }

        // Rule: Service may depend on a product's stock
        if ($this->item_type === ItemType::SERVICE) {
            $serviceDetail = $this->service;
            if ($serviceDetail && $serviceDetail->required_product_id) {
                // Access the product relationship of the required item
                return $serviceDetail->requiredProduct 
                    && $serviceDetail->requiredProduct->product 
                    && $serviceDetail->requiredProduct->product->stock_quantity > 0;
            }
        }

        return true;
    }
}