<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_without_valid_signature_is_rejected_and_order_stays_pending(): void
    {
        config([
            'services.payment.webhook_gateway' => 'midtrans',
            'services.midtrans.server_key' => 'test-server-key',
        ]);

        $order = $this->createPendingOrder();

        $this->postJson('/api/payment/webhook', [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => 'invalid-signature',
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-invalid',
            'payment_type' => 'bank_transfer',
        ])->assertUnauthorized()
            ->assertJson(['error' => 'Invalid webhook signature']);

        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_valid_midtrans_signature_marks_order_paid(): void
    {
        Event::fake();

        config([
            'services.payment.webhook_gateway' => 'midtrans',
            'services.midtrans.server_key' => 'test-server-key',
        ]);

        $order = $this->createPendingOrder();
        $grossAmount = number_format((float) $order->total_amount, 2, '.', '');
        $signature = hash('sha512', $order->order_number . '200' . $grossAmount . 'test-server-key');

        $this->postJson('/api/payment/webhook', [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-valid',
            'payment_type' => 'bank_transfer',
        ])->assertOk()
            ->assertJson([
                'success' => true,
                'order_number' => $order->order_number,
            ]);

        $order->refresh();

        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('trx-valid', $order->payment_reference);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $order->buyer_id,
            'judul' => 'Pembayaran berhasil',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $order->farmer_id,
            'judul' => 'Pesanan sudah dibayar',
        ]);
    }

    private function createPendingOrder(): Order
    {
        $buyerRole = Role::create(['name' => 'buyer', 'display_name' => 'Pembeli']);
        $farmerRole = Role::create(['name' => 'farmer', 'display_name' => 'Petani']);

        $buyer = User::create([
            'role_id' => $buyerRole->id,
            'name' => 'Buyer Test',
            'email' => 'buyer.test@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $farmer = User::create([
            'role_id' => $farmerRole->id,
            'name' => 'Farmer Test',
            'email' => 'farmer.test@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        return Order::create([
            'order_number' => 'AGR-TEST-0001',
            'buyer_id' => $buyer->id,
            'farmer_id' => $farmer->id,
            'subtotal' => 90000,
            'shipping_cost' => 10000,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'shipping_address' => 'Alamat Test',
        ]);
    }
}
