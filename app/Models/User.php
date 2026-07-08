<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'profile_photo_path',
        'phone',
        'address',
        'latitude',
        'longitude',
        'province',
        'district',
        'sub_district',
        'village',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
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

    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'from_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(ChatMessage::class, 'to_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasRole(string $role): bool
    {
        return $this->role?->name === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
    public function isAdminMaster(): bool
    {
        return $this->hasRole('admin_master');
    }
    public function isAdminPanelUser(): bool
    {
        return $this->isAdmin() || $this->isAdminMaster();
    }
    public function isFarmer(): bool
    {
        return $this->hasRole('farmer');
    }
    public function isBuyer(): bool
    {
        return $this->hasRole('buyer');
    }
    public function isDistributor(): bool
    {
        return $this->hasRole('distributor');
    }

    public function isVerifiedFarmer(): bool
    {
        return $this->isFarmer() && $this->farmerProfile?->verification_status === 'verified';
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        if (Str::startsWith($this->profile_photo_path, ['http://', 'https://'])) {
            return $this->profile_photo_path;
        }

        if (Str::startsWith($this->profile_photo_path, ['/storage/', 'storage/'])) {
            $path = Str::after(ltrim($this->profile_photo_path, '/'), 'storage/');

            return Storage::disk('public')->exists($path) ? asset('storage/' . $path) : null;
        }

        if (Str::startsWith($this->profile_photo_path, ['/images/', 'images/'])) {
            $path = ltrim($this->profile_photo_path, '/');

            return file_exists(public_path($path)) ? asset($path) : null;
        }

        $path = ltrim($this->profile_photo_path, '/');

        return Storage::disk('public')->exists($path) ? asset('storage/' . $path) : null;
    }
}
