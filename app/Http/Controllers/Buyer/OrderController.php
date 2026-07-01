<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderNotificationService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayService $paymentGatewayService,
        private readonly OrderNotificationService $orderNotifications,
    )
    {
    }

    public function index(): View
    {
        $orders = Order::with('farmer')
            ->where('buyer_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('buyer.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorizeBuyerOrder($order);

        $order->load(['farmer', 'items.product']);

        return view('buyer.orders.show', compact('order'));
    }

    public function complete(Order $order): RedirectResponse
    {
        $this->authorizeBuyerOrder($order);

        if ($order->order_status !== 'delivered') {
            return back()->with('error', 'Pesanan belum terkirim.');
        }

        $order->update([
            'order_status'   => 'completed',
            'delivered_at'   => now(),
        ]);

        $this->orderNotifications->orderCompleted($order->fresh(['buyer', 'farmer']));

        return back()->with('success', 'Pesanan berhasil diselesaikan. Terima kasih!');
    }

    public function pay(Order $order): RedirectResponse
    {
        $this->authorizeBuyerOrder($order);

        try {
            $order = $this->paymentGatewayService->createPayment($order);

            if ($order->payment_checkout_url) {
                return redirect()->away($order->payment_checkout_url);
            }

            return redirect()->route('buyer.orders.show', $order)
                ->with('success', 'Pembayaran otomatis diset paid dalam mode demo.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->authorizeBuyerOrder($order);

        if (!in_array($order->order_status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Pesanan sudah tidak dapat dibatalkan.');
        }

        $order->update([
            'order_status'   => 'cancelled',
            'payment_status' => 'failed',
        ]);

        $this->orderNotifications->orderCancelled($order->fresh(['buyer', 'farmer']));

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }

    private function authorizeBuyerOrder(Order $order): void
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }
    }
}
