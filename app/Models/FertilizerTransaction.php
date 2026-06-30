<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FertilizerTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number', 'farmer_id', 'distributor_id', 'fertilizer_type_id',
        'fertilizer_quota_id', 'requested_kg', 'approved_kg', 'price_per_kg',
        'total_amount', 'status', 'rejection_reason', 'approved_at',
        'dispensed_at', 'processed_by', 'tracking_status', 'tracking_latitude',
        'tracking_longitude', 'tracking_accuracy', 'tracking_note',
        'tracking_started_at', 'tracking_updated_at',
    ];

    protected $casts = [
        'approved_at'  => 'datetime',
        'dispensed_at' => 'datetime',
        'tracking_started_at' => 'datetime',
        'tracking_updated_at' => 'datetime',
        'price_per_kg' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tracking_latitude' => 'decimal:8',
        'tracking_longitude' => 'decimal:8',
        'tracking_accuracy' => 'decimal:2',
    ];

    public function farmer()        { return $this->belongsTo(User::class, 'farmer_id'); }
    public function distributor()   { return $this->belongsTo(User::class, 'distributor_id'); }
    public function fertilizerType() { return $this->belongsTo(FertilizerType::class); }
    public function quota()         { return $this->belongsTo(FertilizerQuota::class, 'fertilizer_quota_id'); }
    public function processedBy()   { return $this->belongsTo(User::class, 'processed_by'); }
}
