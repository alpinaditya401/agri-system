<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FertilizerQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id', 'fertilizer_type_id', 'year', 'season',
        'allocated_kg', 'used_kg', 'quota_expires_at', 'allocated_by',
    ];

    protected $casts = [
        'quota_expires_at' => 'datetime',
    ];

    public function farmer()       { return $this->belongsTo(User::class, 'farmer_id'); }
    public function fertilizerType() { return $this->belongsTo(FertilizerType::class); }
    public function allocatedBy()  { return $this->belongsTo(User::class, 'allocated_by'); }

    /**
     * Accessor for remaining_kg (mirrors the DB stored column).
     * Falls back to computed value if stored column isn't available.
     */
    public function getRemainingKgAttribute(): int
    {
        return max(0, ($this->allocated_kg ?? 0) - ($this->used_kg ?? 0));
    }

    public function isExhausted(): bool
    {
        return $this->remaining_kg <= 0;
    }
}
