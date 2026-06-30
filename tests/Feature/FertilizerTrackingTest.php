<?php

namespace Tests\Feature;

use App\Models\FertilizerQuota;
use App\Models\FertilizerTransaction;
use App\Models\FertilizerType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FertilizerTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_distributor_can_update_and_farmer_can_view_live_tracking(): void
    {
        $farmerRole = Role::create(['name' => 'farmer', 'display_name' => 'Petani']);
        $distributorRole = Role::create(['name' => 'distributor', 'display_name' => 'Distributor']);
        $buyerRole = Role::create(['name' => 'buyer', 'display_name' => 'Pembeli']);

        $farmer = User::create([
            'role_id' => $farmerRole->id,
            'name' => 'Petani Tracking',
            'email' => 'farmer.tracking@example.com',
            'password' => 'password',
            'latitude' => -7.7956,
            'longitude' => 110.3695,
            'is_active' => true,
        ]);

        $distributor = User::create([
            'role_id' => $distributorRole->id,
            'name' => 'Distributor Tracking',
            'email' => 'distributor.tracking@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $otherBuyer = User::create([
            'role_id' => $buyerRole->id,
            'name' => 'Buyer Lain',
            'email' => 'buyer.other@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $type = FertilizerType::create([
            'code' => 'UREA',
            'name' => 'Urea',
            'subsidy_price_per_kg' => 2500,
            'is_active' => true,
        ]);

        $quota = FertilizerQuota::create([
            'farmer_id' => $farmer->id,
            'fertilizer_type_id' => $type->id,
            'year' => now()->year,
            'season' => 'MT1',
            'allocated_kg' => 100,
            'used_kg' => 0,
        ]);

        $transaction = FertilizerTransaction::create([
            'transaction_number' => 'TRX-TRACK-001',
            'farmer_id' => $farmer->id,
            'distributor_id' => $distributor->id,
            'fertilizer_type_id' => $type->id,
            'fertilizer_quota_id' => $quota->id,
            'requested_kg' => 25,
            'approved_kg' => 25,
            'price_per_kg' => 2500,
            'total_amount' => 62500,
            'status' => 'approved',
        ]);

        $this->actingAs($distributor)
            ->patchJson(route('api.fertilizer-tracking.update', $transaction), [
                'latitude' => -7.78000000,
                'longitude' => 110.39000000,
                'accuracy' => 12,
                'tracking_status' => 'on_the_way',
            ])
            ->assertOk()
            ->assertJsonPath('data.tracking.has_location', true)
            ->assertJsonPath('data.tracking.status', 'on_the_way');

        $this->actingAs($farmer)
            ->getJson(route('api.fertilizer-tracking.show', $transaction))
            ->assertOk()
            ->assertJsonPath('data.tracking.latitude', -7.78)
            ->assertJsonPath('data.distributor.name', 'Distributor Tracking');

        $this->actingAs($otherBuyer)
            ->getJson(route('api.fertilizer-tracking.show', $transaction))
            ->assertForbidden();
    }
}
