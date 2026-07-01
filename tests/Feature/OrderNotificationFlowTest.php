<?php

namespace Tests\Feature;

use App\Models\FarmerProfile;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderNotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_actions_create_notifications(): void
    {
        [$buyer, $farmer] = $this->users();

        $order = $this->order($buyer, $farmer, [
            'payment_status' => 'paid',
            'order_status' => 'pending',
            'paid_at' => now(),
        ]);

        $this->actingAs($farmer)
            ->patch(route('farmer.orders.confirm', $order))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $buyer->id,
            'tipe' => 'arrived',
            'judul' => 'Pesanan dikonfirmasi',
        ]);

        $this->actingAs($farmer)
            ->patch(route('farmer.orders.ship', $order->fresh()), [
                'tracking_number' => 'TRK-001',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $buyer->id,
            'judul' => 'Pesanan dikirim',
        ]);

        $order->refresh()->update(['order_status' => 'delivered']);

        $this->actingAs($buyer)
            ->patch(route('buyer.orders.complete', $order))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $farmer->id,
            'judul' => 'Pesanan diselesaikan',
        ]);
    }

    public function test_buyer_cancel_creates_farmer_notification(): void
    {
        [$buyer, $farmer] = $this->users();
        $order = $this->order($buyer, $farmer);

        $this->actingAs($buyer)
            ->patch(route('buyer.orders.cancel', $order))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $farmer->id,
            'judul' => 'Pesanan dibatalkan',
        ]);
    }

    public function test_farmer_cannot_confirm_unpaid_order(): void
    {
        [$buyer, $farmer] = $this->users();
        $order = $this->order($buyer, $farmer);

        $this->actingAs($farmer)
            ->patch(route('farmer.orders.confirm', $order))
            ->assertSessionHas('error');

        $this->assertSame('pending', $order->fresh()->order_status);
    }

    private function users(): array
    {
        $buyerRole = Role::create(['name' => 'buyer', 'display_name' => 'Pembeli']);
        $farmerRole = Role::create(['name' => 'farmer', 'display_name' => 'Petani']);

        $buyer = User::create([
            'role_id' => $buyerRole->id,
            'name' => 'Buyer Order',
            'email' => 'buyer.order@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $farmer = User::create([
            'role_id' => $farmerRole->id,
            'name' => 'Farmer Order',
            'email' => 'farmer.order@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        FarmerProfile::create([
            'user_id' => $farmer->id,
            'nik' => '3201010101010199',
            'verification_status' => 'verified',
        ]);

        return [$buyer, $farmer];
    }

    private function order(User $buyer, User $farmer, array $overrides = []): Order
    {
        return Order::create([
            'order_number' => 'AGR-NOTIF-' . Str::upper(Str::random(6)),
            'buyer_id' => $buyer->id,
            'farmer_id' => $farmer->id,
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'shipping_address' => 'Alamat Test',
            ...$overrides,
        ]);
    }
}
