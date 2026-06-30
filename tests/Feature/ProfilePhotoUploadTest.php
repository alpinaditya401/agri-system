<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_and_remove_profile_photo(): void
    {
        Storage::fake('public');

        $role = Role::create([
            'name' => 'buyer',
            'display_name' => 'Pembeli',
        ]);

        $user = User::create([
            'role_id' => $role->id,
            'name' => 'Pembeli Test',
            'email' => 'buyer.test@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $path = tempnam(sys_get_temp_dir(), 'profile-photo-');
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        ));

        $photo = new UploadedFile($path, 'avatar.png', 'image/png', null, true);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'profile_photo' => $photo,
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);

        $storedPath = $user->profile_photo_path;

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'remove_profile_photo' => '1',
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertNull($user->profile_photo_path);
        Storage::disk('public')->assertMissing($storedPath);
    }
}
