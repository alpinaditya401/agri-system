<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUrlFallbackTest extends TestCase
{
    public function test_product_main_image_url_ignores_missing_storage_file(): void
    {
        Storage::fake('public');

        $product = new Product(['main_image' => 'products/missing.jpg']);

        $this->assertNull($product->main_image_url);

        Storage::disk('public')->put('products/existing.jpg', 'image-bytes');
        $product->main_image = 'products/existing.jpg';

        $this->assertStringContainsString('/storage/products/existing.jpg', $product->main_image_url);
    }

    public function test_user_profile_photo_url_ignores_missing_storage_file(): void
    {
        Storage::fake('public');

        $user = new User(['profile_photo_path' => 'profile-photos/missing.png']);

        $this->assertNull($user->profile_photo_url);

        Storage::disk('public')->put('profile-photos/existing.png', 'image-bytes');
        $user->profile_photo_path = 'profile-photos/existing.png';

        $this->assertStringContainsString('/storage/profile-photos/existing.png', $user->profile_photo_url);
    }

    public function test_product_gallery_image_url_ignores_missing_storage_file(): void
    {
        Storage::fake('public');

        $image = new ProductImage(['image_path' => 'products/gallery-missing.jpg']);

        $this->assertNull($image->image_url);

        Storage::disk('public')->put('products/gallery-existing.jpg', 'image-bytes');
        $image->image_path = 'products/gallery-existing.jpg';

        $this->assertStringContainsString('/storage/products/gallery-existing.jpg', $image->image_url);
    }
}
