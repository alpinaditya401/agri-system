<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    public function __construct(
        private readonly PaymentSettingsService $settings,
        private readonly OrderService $orderService
    )
    {
    }

    public function isConfigured(): bool
    {
        return match ($this->gateway()) {
            'demo' => true,
            'midtrans' => $this->settings->isMidtransConfigured(),
            default => false,
        };
    }

    public function createPayment(Order $order): Order
    {
        if ($order->payment_status !== 'pending') {
            throw new \RuntimeException('Pembayaran hanya bisa dibuat untuk order yang masih pending.');
        }

        if ($order->order_status === 'cancelled') {
            throw new \RuntimeException('Order yang dibatalkan tidak bisa dibayar.');
        }

        if (!$this->isConfigured()) {
            throw new \RuntimeException('Konfigurasi payment gateway belum lengkap. Isi Midtrans Server Key di menu Admin Master > Payment Gateway.');
        }

        if ($this->gateway() === 'demo') {
            return $this->createDemoAutoPaidPayment($order);
        }

        if (
            $order->payment_gateway === 'midtrans'
            && $order->payment_checkout_url
            && (!$order->payment_expires_at || $order->payment_expires_at->isFuture())
        ) {
            return $order;
        }

        return $this->createMidtransSnapPayment($order);
    }

    private function createDemoAutoPaidPayment(Order $order): Order
    {
        $reference = 'DEMO-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));

        $order = $this->orderService->confirmPayment($order->order_number, $reference, 'demo_auto_paid');

        $order->update([
            'payment_gateway' => 'demo',
            'payment_token' => null,
            'payment_checkout_url' => null,
            'payment_expires_at' => null,
        ]);

        return $order->fresh(['buyer', 'farmer', 'items.product']);
    }

    private function createMidtransSnapPayment(Order $order): Order
    {
        $order->loadMissing(['buyer', 'items']);

        $grossAmount = (int) round((float) $order->total_amount);

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => Str::limit((string) $order->buyer?->name, 100, ''),
                'email' => $order->buyer?->email,
                'phone' => $order->buyer?->phone,
                'billing_address' => [
                    'address' => Str::limit((string) $order->shipping_address, 200, ''),
                ],
                'shipping_address' => [
                    'address' => Str::limit((string) $order->shipping_address, 200, ''),
                ],
            ],
            'item_details' => $this->buildMidtransItemDetails($order, $grossAmount),
            'callbacks' => [
                'finish' => route('buyer.orders.show', $order),
                'error' => route('buyer.orders.show', $order),
                'pending' => route('buyer.orders.show', $order),
            ],
            'page_expiry' => [
                'duration' => 3,
                'unit' => 'hours',
            ],
        ];

        $response = Http::withBasicAuth((string) $this->settings->midtransServerKey(), '')
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->post($this->midtransSnapUrl(), $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Gagal membuat transaksi Midtrans: ' . $response->body());
        }

        $data = $response->json();
        $token = $data['token'] ?? null;
        $redirectUrl = $data['redirect_url'] ?? null;

        if (!$token || !$redirectUrl) {
            throw new \RuntimeException('Response Midtrans tidak berisi token atau redirect_url.');
        }

        $order->update([
            'payment_gateway' => 'midtrans',
            'payment_token' => $token,
            'payment_checkout_url' => $redirectUrl,
            'payment_expires_at' => now()->addHours(3),
        ]);

        return $order->fresh(['buyer', 'farmer', 'items.product']);
    }

    private function buildMidtransItemDetails(Order $order, int $grossAmount): array
    {
        $items = [];

        foreach ($order->items as $item) {
            $items[] = [
                'id' => (string) $item->product_id,
                'price' => (int) round((float) $item->price_per_unit),
                'quantity' => (int) $item->quantity,
                'name' => Str::limit($item->product_name, 50, ''),
            ];
        }

        if ((float) $order->shipping_cost > 0) {
            $items[] = [
                'id' => 'shipping',
                'price' => (int) round((float) $order->shipping_cost),
                'quantity' => 1,
                'name' => 'Ongkos kirim',
            ];
        }

        if ((float) $order->tax_amount > 0) {
            $items[] = [
                'id' => 'tax',
                'price' => (int) round((float) $order->tax_amount),
                'quantity' => 1,
                'name' => 'Pajak',
            ];
        }

        $itemTotal = collect($items)->sum(fn(array $item) => $item['price'] * $item['quantity']);

        if ($itemTotal !== $grossAmount) {
            $items[] = [
                'id' => 'rounding-adjustment',
                'price' => $grossAmount - $itemTotal,
                'quantity' => 1,
                'name' => 'Penyesuaian total',
            ];
        }

        return $items;
    }

    private function midtransSnapUrl(): string
    {
        return $this->settings->midtransSnapUrl() . '/snap/v1/transactions';
    }

    private function gateway(): string
    {
        return $this->settings->gateway();
    }
}
