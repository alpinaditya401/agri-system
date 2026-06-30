<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'nik', 'farmer_group_id', 'farmer_group_name',
        'land_area_hectares', 'main_commodity', 'verification_status',
        'rejection_reason', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'verified_at'        => 'datetime',
        'land_area_hectares' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function fertilizerQuotas()
    {
        return $this->hasMany(FertilizerQuota::class, 'farmer_id', 'user_id');
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }
}
