<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'farmer_id', 'category_id', 'name', 'slug', 'description',
        'price_per_unit', 'unit', 'stock_quantity', 'minimum_order',
        'main_image', 'origin_province', 'origin_district',
        'origin_lat', 'origin_lng', 'status', 'is_featured',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'origin_lat'     => 'decimal:8',
        'origin_lng'     => 'decimal:8',
        'is_featured'    => 'boolean',
    ];

    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getMainImageUrlAttribute(): ?string
    {
        if (!$this->main_image) {
            return null;
        }

        if (Str::startsWith($this->main_image, ['http://', 'https://'])) {
            return $this->main_image;
        }

        if (Str::startsWith($this->main_image, '/')) {
            return url($this->main_image);
        }

        if (Str::startsWith($this->main_image, ['storage/', 'images/'])) {
            return asset($this->main_image);
        }

        return asset('storage/' . ltrim($this->main_image, '/'));
    }
}
