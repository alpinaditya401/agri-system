<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * OrderService
 *
 * Handles the full C2C/B2C checkout flow:
 *   Cart → Validate Stock → Generate Invoice → Deduct Stock on Payment
 */
class OrderService
{
    /**
     * Checkout: convert cart items into an Order (Invoice).
     *
     * Stock is NOT deducted here — only reserved mentally via invoice status.
     * Stock deduction happens in confirmPayment() after payment gateway callback.
     *
     * @throws \RuntimeException if stock is insufficient for any item
     */
    public function checkoutFromCart(int $buyerId, array $shippingData): Order
    {
        $cartItems = Cart::with('product.farmer')
            ->where('buyer_id', $buyerId)
            ->get();

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException('Keranjang belanja kosong.');
        }

        // Group by farmer (each farmer = separate order)
        $grouped = $cartItems->groupBy(fn($item) => $item->product->farmer_id);

        if ($grouped->count() > 1) {
            throw new \RuntimeException(
                'Checkout sementara hanya mendukung produk dari satu petani dalam satu transaksi. '
                . 'Silakan checkout produk per petani.'
            );
        }

        DB::beginTransaction();
        try {
            $orders = [];

            foreach ($grouped as $farmerId => $items) {
                $order = $this->createOrderForFarmer($buyerId, $farmerId, $items, $shippingData);
                $orders[] = $order;
            }

            // Clear the cart after all orders created
            Cart::where('buyer_id', $buyerId)->delete();

            DB::commit();

            // Return first order (or return collection for multi-seller checkout)
            return $orders[0];

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Checkout failed', ['buyer_id' => $buyerId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a single order for one farmer's items.
     */
    private function createOrderForFarmer(
        int $buyerId,
        int $farmerId,
        Collection $items,
        array $shippingData
    ): Order {
        // ── Validate stock with row-level lock ────────────────────────────────
        foreach ($items as $item) {
            $product = Product::lockForUpdate()->findOrFail($item->product_id);

            if ($product->stock_quantity < $item->quantity) {
                throw new \RuntimeException(
                    "Stok produk '{$product->name}' tidak mencukupi. "
                    . "Tersedia: {$product->stock_quantity} {$product->unit}, "
                    . "diminta: {$item->quantity} {$product->unit}."
                );
            }

            if ($product->status !== 'active') {
                throw new \RuntimeException(
                    "Produk '{$product->name}' sudah tidak tersedia."
                );
            }
        }

        // ── Calculate totals ──────────────────────────────────────────────────
        $subtotal     = $items->sum(fn($i) => $i->product->price_per_unit * $i->quantity);
        $shippingCost = $shippingData['shipping_cost'] ?? 0;
        $taxAmount    = 0;
        $total        = $subtotal + $shippingCost + $taxAmount;

        // ── Create order (invoice) ────────────────────────────────────────────
        $order = Order::create([
            'order_number'    => $this->generateOrderNumber(),
            'buyer_id'        => $buyerId,
            'farmer_id'       => $farmerId,
            'subtotal'        => $subtotal,
            'shipping_cost'   => $shippingCost,
            'tax_amount'      => $taxAmount,
            'total_amount'    => $total,
            'payment_status'  => 'pending',
            'order_status'    => 'pending',
            'shipping_address'=> $shippingData['address'],
            'shipping_method' => $shippingData['method'] ?? null,
            'buyer_notes'     => $shippingData['notes'] ?? null,
        ]);

        // ── Create order items (snapshot product data) ────────────────────────
        foreach ($items as $item) {
            OrderItem::create([
                'order_id'      => $order->id,
                'product_id'    => $item->product_id,
                'product_name'  => $item->product->name,
                'price_per_unit'=> $item->product->price_per_unit,
                'unit'          => $item->product->unit,
                'quantity'      => $item->quantity,
                'subtotal'      => $item->product->price_per_unit * $item->quantity,
            ]);
        }

        return $order;
    }

    /**
     * Confirm payment — deduct stock and update order status.
     *
     * Called by the payment gateway webhook callback.
     * Uses DB transaction + row-level locking to prevent overselling.
     */
    public function confirmPayment(string $orderNumber, string $paymentReference, string $method): Order
    {
        DB::beginTransaction();
        try {
            $order = Order::with('items')
                ->where('order_number', $orderNumber)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->payment_status === 'paid') {
                if ($order->payment_reference && $order->payment_reference !== $paymentReference) {
                    DB::rollBack();
                    throw new \RuntimeException('Order ini sudah terbayar dengan referensi berbeda.');
                }

                DB::commit();
                return $order->fresh();
            }

            // ── Deduct stock for each item ────────────────────────────────────
            foreach ($order->items as $item) {
                $affected = Product::where('id', $item->product_id)
                    ->where('stock_quantity', '>=', $item->quantity)
                    ->decrement('stock_quantity', $item->quantity);

                if (!$affected) {
                    DB::rollBack();
                    throw new \RuntimeException(
                        "Gagal mengurangi stok produk ID {$item->product_id}. "
                        . "Stok mungkin sudah habis."
                    );
                }

                // Auto mark as sold_out if stock hits zero
                Product::where('id', $item->product_id)
                    ->where('stock_quantity', 0)
                    ->update(['status' => 'sold_out']);
            }

            // ── Update order status ───────────────────────────────────────────
            $order->update([
                'payment_status'    => 'paid',
                'order_status'      => 'confirmed',
                'payment_method'    => $method,
                'payment_reference' => $paymentReference,
                'paid_at'           => now(),
            ]);

            DB::commit();

            // Dispatch events (notification to farmer, buyer receipt email, etc.)
            event(new \App\Events\OrderPaid($order));

            return $order->fresh();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Payment confirmation failed', [
                'order_number' => $orderNumber,
                'error'        => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Add item to cart or update quantity if already exists.
     */
    public function addToCart(int $buyerId, int $productId, int $quantity): Cart
    {
        $product = Product::where('id', $productId)
            ->where('status', 'active')
            ->firstOrFail();

        if ($product->stock_quantity < $quantity) {
            throw new \RuntimeException(
                "Stok tidak mencukupi. Tersedia: {$product->stock_quantity} {$product->unit}."
            );
        }

        return Cart::updateOrCreate(
            ['buyer_id' => $buyerId, 'product_id' => $productId],
            ['quantity' => $quantity]
        );
    }

    /**
     * Generate unique order number.
     * Format: AGR-YYYYMMDD-NNNN
     */
    private function generateOrderNumber(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "AGR-{$date}-";

        $last = Order::where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
