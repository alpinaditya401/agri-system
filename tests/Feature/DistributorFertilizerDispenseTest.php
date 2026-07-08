<?php

namespace Tests\Feature;

use App\Models\FertilizerQuota;
use App\Models\FertilizerStock;
use App\Models\FertilizerTransaction;
use App\Models\FertilizerType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributorFertilizerDispenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_dispense_url_redirects_to_transaction_detail(): void
    {
        [$distributor, , , $transaction] = $this->approvedTransaction();

        $this->actingAs($distributor)
            ->get(route('distributor.fertilizer.dispense.redirect', $transaction))
            ->assertRedirect(route('distributor.fertilizer.show', $transaction))
            ->assertSessionHas('error');

        $this->assertSame('approved', $transaction->fresh()->status);
    }

    public function test_distributor_can_dispense_approved_fertilizer_transaction(): void
    {
        [$distributor, , $quota, $transaction] = $this->approvedTransaction();

        FertilizerStock::create([
            'distributor_id' => $distributor->id,
            'fertilizer_type_id' => $transaction->fertilizer_type_id,
            'stock_kg' => 100,
            'reserved_kg' => 25,
        ]);

        $this->actingAs($distributor)
            ->patch(route('distributor.fertilizer.dispense', $transaction))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('dispensed', $transaction->fresh()->status);
        $this->assertSame(25, $quota->fresh()->used_kg);

        $this->assertDatabaseHas('fertilizer_stocks', [
            'distributor_id' => $distributor->id,
            'fertilizer_type_id' => $transaction->fertilizer_type_id,
            'stock_kg' => 75,
            'reserved_kg' => 0,
        ]);
    }

    public function test_dispense_returns_error_when_stock_is_missing(): void
    {
        [$distributor, , , $transaction] = $this->approvedTransaction();

        $this->actingAs($distributor)
            ->patch(route('distributor.fertilizer.dispense', $transaction))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('approved', $transaction->fresh()->status);
    }

    private function approvedTransaction(): array
    {
        $farmerRole = Role::create(['name' => 'farmer', 'display_name' => 'Petani']);
        $distributorRole = Role::create(['name' => 'distributor', 'display_name' => 'Distributor']);

        $farmer = User::create([
            'role_id' => $farmerRole->id,
            'name' => 'Petani Serah',
            'email' => 'petani.serah@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $distributor = User::create([
            'role_id' => $distributorRole->id,
            'name' => 'Distributor Serah',
            'email' => 'distributor.serah@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $type = FertilizerType::create([
            'code' => 'UREA',
            'name' => 'Pupuk Urea',
            'subsidy_price_per_kg' => 2500,
            'is_active' => true,
        ]);

        $quota = FertilizerQuota::create([
            'farmer_id' => $farmer->id,
            'fertilizer_type_id' => $type->id,
            'year' => now()->year,
            'season' => now()->month >= 10 || now()->month <= 3 ? 'MT1' : 'MT2',
            'allocated_kg' => 100,
            'used_kg' => 0,
        ]);

        $transaction = FertilizerTransaction::create([
            'transaction_number' => 'PUPUK-TEST-0001',
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

        return [$distributor, $farmer, $quota, $transaction];
    }
}
