<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id', 'name', 'email', 'password', 'phone', 'address',
        'latitude', 'longitude', 'province', 'district', 'sub_district',
        'village', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
        'latitude'          => 'decimal:8',
        'longitude'         => 'decimal:8',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function farmerProfile()
    {
        return $this->hasOne(FarmerProfile::class);
    }

    public function distributorProfile()
    {
        return $this->hasOne(DistributorProfile::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'farmer_id');
    }

    public function fertilizerStocks()
    {
        return $this->hasMany(FertilizerStock::class, 'distributor_id');
    }

    public function fertilizerQuotas()
    {
        return $this->hasMany(FertilizerQuota::class, 'farmer_id');
    }

    public function buyerOrders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function farmerOrders()
    {
        return $this->hasMany(Order::class, 'farmer_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasRole(string $role): bool
    {
        return $this->role?->name === $role;
    }

    public function isAdmin(): bool      { return $this->hasRole('admin'); }
    public function isFarmer(): bool     { return $this->hasRole('farmer'); }
    public function isBuyer(): bool      { return $this->hasRole('buyer'); }
    public function isDistributor(): bool { return $this->hasRole('distributor'); }

    public function isVerifiedFarmer(): bool
    {
        return $this->isFarmer() && $this->farmerProfile?->verification_status === 'verified';
    }
}
