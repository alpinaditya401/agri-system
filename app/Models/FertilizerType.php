<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FertilizerType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'subsidy_price_per_kg', 'market_price_per_kg',
        'description', 'is_active',
    ];

    protected $casts = [
        'subsidy_price_per_kg' => 'decimal:2',
        'market_price_per_kg'  => 'decimal:2',
        'is_active'            => 'boolean',
    ];

    public function stocks()
    {
        return $this->hasMany(FertilizerStock::class);
    }

    public function quotas()
    {
        return $this->hasMany(FertilizerQuota::class);
    }

    public function transactions()
    {
        return $this->hasMany(FertilizerTransaction::class);
    }
}
