<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'image_path', 'sort_order'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (Str::startsWith($this->image_path, ['http://', 'https://'])) {
            return $this->image_path;
        }

        if (Str::startsWith($this->image_path, ['/storage/', 'storage/'])) {
            $path = Str::after(ltrim($this->image_path, '/'), 'storage/');

            return Storage::disk('public')->exists($path) ? asset('storage/' . $path) : null;
        }

        if (Str::startsWith($this->image_path, ['/images/', 'images/'])) {
            $path = ltrim($this->image_path, '/');

            return file_exists(public_path($path)) ? asset($path) : null;
        }

        $path = ltrim($this->image_path, '/');

        return Storage::disk('public')->exists($path) ? asset('storage/' . $path) : null;
    }
}
