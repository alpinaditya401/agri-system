<?php

namespace Tests\Feature;

use App\Models\FarmerProfile;
use App\Models\FertilizerQuota;
use App\Models\FertilizerType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FertilizerQuotaReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_quota_reminder_creates_low_stock_notification_once_per_day(): void
    {
        $farmerRole = Role::create(['name' => 'farmer', 'display_name' => 'Petani']);

        $farmer = User::create([
            'role_id' => $farmerRole->id,
            'name' => 'Petani Kuota',
            'email' => 'petani.kuota@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        FarmerProfile::create([
            'user_id' => $farmer->id,
            'nik' => '3201010101010123',
            'farmer_group_name' => 'Tani Uji',
            'verification_status' => 'verified',
        ]);

        $type = FertilizerType::create([
            'code' => 'UREA',
            'name' => 'Pupuk Urea',
            'subsidy_price_per_kg' => 2250,
            'is_active' => true,
        ]);

        FertilizerQuota::create([
            'farmer_id' => $farmer->id,
            'fertilizer_type_id' => $type->id,
            'year' => now()->year,
            'season' => $this->currentSeason(),
            'allocated_kg' => 200,
            'used_kg' => 170,
        ]);

        $this->artisan('fertilizer:quota-reminder')
            ->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $farmer->id,
            'tipe' => 'low_stock',
            'judul' => 'Kuota pupuk menipis',
        ]);

        $this->artisan('fertilizer:quota-reminder')
            ->assertSuccessful();

        $this->assertSame(
            1,
            \App\Models\Notification::where('user_id', $farmer->id)
                ->where('judul', 'Kuota pupuk menipis')
                ->count()
        );
    }

    private function currentSeason(): string
    {
        $month = now()->month;

        return ($month >= 10 || $month <= 3) ? 'MT1' : 'MT2';
    }
}
