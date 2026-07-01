<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributorApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_distributor_registration_waits_for_admin_before_login(): void
    {
        Role::create(['name' => 'buyer', 'display_name' => 'Pembeli']);
        Role::create(['name' => 'farmer', 'display_name' => 'Petani']);
        Role::create(['name' => 'distributor', 'display_name' => 'Distributor']);
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);

        $admin = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Admin Distributor',
            'email' => 'admin.distributor.direct@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->post(route('register'), [
            'role' => 'distributor',
            'name' => 'Distributor Baru',
            'email' => 'distributor.baru@example.com',
            'phone' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
            'company_name' => 'CV Pupuk Baru',
            'license_number' => 'NIB-DIRECT-001',
            'latitude' => '-6.30500000',
            'longitude' => '107.30000000',
        ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('distributor_profiles', [
            'company_name' => 'CV Pupuk Baru',
            'verification_status' => 'pending',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'judul' => 'Pengajuan distributor baru',
        ]);

        $this->post(route('login'), [
            'email' => 'distributor.baru@example.com',
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        $distributor = User::where('email', 'distributor.baru@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.distributors.verify.approve', $distributor))
            ->assertRedirect();

        $this->post(route('login'), [
            'email' => 'distributor.baru@example.com',
            'password' => 'password',
        ])
            ->assertRedirect(route('dashboard'));
    }

    public function test_buyer_can_apply_as_distributor_and_admin_can_approve(): void
    {
        $buyerRole = Role::create(['name' => 'buyer', 'display_name' => 'Pembeli']);
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $distributorRole = Role::create(['name' => 'distributor', 'display_name' => 'Distributor']);

        $admin = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Admin Verifikasi',
            'email' => 'admin.verify@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $buyer = User::create([
            'role_id' => $buyerRole->id,
            'name' => 'Calon Distributor',
            'email' => 'buyer.distributor@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->actingAs($buyer)
            ->get(route('buyer.become-distributor.create'))
            ->assertOk();

        $this->actingAs($buyer)
            ->post(route('buyer.become-distributor.store'), [
                'company_name' => 'CV Pupuk Makmur',
                'license_number' => 'NIB-12345',
                'storage_capacity_kg' => 5000,
                'phone' => '081234567890',
                'address' => 'Gudang Pupuk No. 1',
                'province' => 'Jawa Barat',
                'district' => 'Karawang',
                'latitude' => '-6.30500000',
                'longitude' => '107.30000000',
            ])
            ->assertRedirect(route('buyer.become-distributor.create'));

        $this->assertDatabaseHas('distributor_profiles', [
            'user_id' => $buyer->id,
            'company_name' => 'CV Pupuk Makmur',
            'verification_status' => 'pending',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'judul' => 'Pengajuan distributor baru',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.distributors.verify.index'))
            ->assertOk()
            ->assertSee('CV Pupuk Makmur');

        $this->actingAs($admin)
            ->patch(route('admin.distributors.verify.approve', $buyer))
            ->assertRedirect();

        $buyer->refresh();

        $this->assertSame($distributorRole->id, $buyer->role_id);
        $this->assertDatabaseHas('distributor_profiles', [
            'user_id' => $buyer->id,
            'verification_status' => 'verified',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $buyer->id,
            'judul' => 'Pengajuan distributor disetujui',
        ]);
    }

    public function test_admin_can_open_agriculture_statistics_report(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);

        $admin = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Admin Statistik',
            'email' => 'admin.stats@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.agriculture'))
            ->assertOk()
            ->assertSee('Statistik Petani &amp; Pupuk', false);
    }
}
