<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SaleDetails;

class Sale extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes for the Sale model.
     * 
     * @var array<int>
     */
    protected $fillable = ['client_id', 'sale_date', 'client_daily_sales_count', 'total_amount'];

    /**
     * Define the relationship between Sale and SaleDetails.
     * A sale can have many details (items/services sold).
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details(): HasMany {
        return $this->hasMany(SaleDetails::class);
    }

    /**
     * Define the relationship between Sale and Client.
     * A sale belongs to a client.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client() {
        return $this->belongsTo(Client::class);
    }
}
