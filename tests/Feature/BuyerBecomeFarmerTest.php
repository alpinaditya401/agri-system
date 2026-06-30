<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerBecomeFarmerTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_register_as_farmer_seller(): void
    {
        $buyerRole = Role::create([
            'name' => 'buyer',
            'display_name' => 'Pembeli',
        ]);

        $farmerRole = Role::create([
            'name' => 'farmer',
            'display_name' => 'Petani',
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Admin',
        ]);

        $admin = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Admin Verifikasi',
            'email' => 'admin.farmer@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $buyer = User::create([
            'role_id' => $buyerRole->id,
            'name' => 'Pembeli Calon Petani',
            'email' => 'buyer.to.farmer@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->actingAs($buyer)
            ->get(route('buyer.become-farmer.create'))
            ->assertOk();

        $this->actingAs($buyer)
            ->post(route('buyer.become-farmer.store'), [
                'nik' => '1234567890123456',
                'farmer_group_id' => null,
                'farmer_group_name' => 'Tani Maju',
                'land_area_hectares' => '1.25',
                'main_commodity' => 'Cabai Merah',
                'phone' => '081234567890',
                'district' => 'Sleman',
                'latitude' => '-7.79560000',
                'longitude' => '110.36950000',
            ])
            ->assertRedirect(route('login'));

        $buyer->refresh();

        $this->assertSame($farmerRole->id, $buyer->role_id);
        $this->assertDatabaseHas('farmer_profiles', [
            'user_id' => $buyer->id,
            'nik' => '1234567890123456',
            'main_commodity' => 'Cabai Merah',
            'verification_status' => 'pending',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'judul' => 'Pengajuan penjual/petani baru',
        ]);

        $this->post(route('login'), [
            'email' => 'buyer.to.farmer@example.com',
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email');

        $this->actingAs($admin)
            ->patch(route('admin.farmers.verify.approve', $buyer))
            ->assertRedirect();

        $this->assertDatabaseHas('farmer_profiles', [
            'user_id' => $buyer->id,
            'verification_status' => 'verified',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $buyer->id,
            'judul' => 'Pengajuan penjual disetujui',
        ]);

        $this->post(route('login'), [
            'email' => 'buyer.to.farmer@example.com',
            'password' => 'password',
        ])
            ->assertRedirect(route('dashboard'));
    }
}
