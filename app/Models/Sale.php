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

    public function details(): HasMany {
        return $this->hasMany(SaleDetails::class);
    }
}
