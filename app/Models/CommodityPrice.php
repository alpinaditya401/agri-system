<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommodityPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'commodity_name', 'commodity_code', 'category', 'price', 'unit',
        'region', 'region_code', 'source', 'price_date', 'raw_data',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'price_date' => 'date',
        'raw_data'   => 'array',
    ];

    /**
     * Scope: latest price per commodity (most recent date).
     */
    public function scopeLatestPerCommodity($query)
    {
        return $query->whereIn('id', function ($sub) {
            $sub->selectRaw('MAX(id)')
                ->from('commodity_prices')
                ->groupBy('commodity_name', 'region');
        });
    }
}
