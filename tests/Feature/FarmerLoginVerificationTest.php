<?php

namespace Tests\Feature;

use App\Models\FarmerProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmerLoginVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_farmer_cannot_login_before_admin_verification(): void
    {
        $farmerRole = Role::create(['name' => 'farmer', 'display_name' => 'Petani']);

        $farmer = User::create([
            'role_id' => $farmerRole->id,
            'name' => 'Petani Pending',
            'email' => 'farmer.pending@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        FarmerProfile::create([
            'user_id' => $farmer->id,
            'nik' => '3201010101010101',
            'land_area_hectares' => 1.5,
            'main_commodity' => 'Cabai',
            'verification_status' => 'pending',
        ]);

        $this->post(route('login'), [
            'email' => 'farmer.pending@example.com',
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        $farmer->farmerProfile->update(['verification_status' => 'verified']);

        $this->post(route('login'), [
            'email' => 'farmer.pending@example.com',
            'password' => 'password',
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($farmer);
    }

    public function test_direct_farmer_registration_waits_for_admin_before_login(): void
    {
        Role::create(['name' => 'farmer', 'display_name' => 'Petani']);
        Role::create(['name' => 'buyer', 'display_name' => 'Pembeli']);
        Role::create(['name' => 'distributor', 'display_name' => 'Distributor']);
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);

        $admin = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Admin Farmer',
            'email' => 'admin.farmer.login@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->post(route('register'), [
            'role' => 'farmer',
            'name' => 'Petani Baru',
            'email' => 'petani.baru@example.com',
            'phone' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
            'nik' => '3202020202020202',
            'land_area_hectares' => 2.25,
            'main_commodity' => 'Padi',
            'district' => 'Sleman',
            'latitude' => '-7.79560000',
            'longitude' => '110.36950000',
        ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('farmer_profiles', [
            'nik' => '3202020202020202',
            'verification_status' => 'pending',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'judul' => 'Pengajuan penjual/petani baru',
        ]);
    }
}
