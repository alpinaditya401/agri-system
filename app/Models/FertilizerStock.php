<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FertilizerStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id', 'fertilizer_type_id', 'stock_kg', 'reserved_kg',
        'batch_number', 'received_date', 'expiry_date',
    ];

    protected $casts = [
        'received_date' => 'date',
        'expiry_date'   => 'date',
    ];

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function fertilizerType()
    {
        return $this->belongsTo(FertilizerType::class);
    }
}
