<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    public function sale(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getDailyPurchase(\DateTimeInterface $date)
    {
        return $this->sale()->whereDate('created_at', $date)->sum('total_amount');
    }
}
