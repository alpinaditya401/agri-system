<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderNotificationService $orderNotifications)
    {
    }

    public function index(Request $request): View
    {
        $allowedStatuses = array_keys(Order::ORDER_STATUS_LABELS);
        $status = $request->query('status');

        if (!in_array($status, $allowedStatuses, true)) {
            $status = null;
        }

        $orders = Order::with('buyer')
            ->where('farmer_id', Auth::id())
            ->when($status, fn($query) => $query->where('order_status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusOptions = Order::ORDER_STATUS_LABELS;

        return view('farmer.orders.index', compact('orders', 'status', 'statusOptions'));
    }

    public function show(Order $order): View
    {
        $this->authorizeFarmerOrder($order);

        $order->load(['buyer', 'items.product']);

        return view('farmer.orders.show', compact('order'));
    }

    public function confirm(Order $order): RedirectResponse
    {
        $this->authorizeFarmerOrder($order);

        if ($order->order_status !== 'pending') {
            return back()->with('error', 'Pesanan sudah tidak dapat dikonfirmasi.');
        }

        if ($order->payment_status !== 'paid') {
            return back()->with('error', 'Pembayaran belum diterima. Jangan proses pesanan sebelum pembayaran lunas.');
        }

        $order->update(['order_status' => 'confirmed']);

        $this->orderNotifications->orderConfirmed($order->fresh(['buyer', 'farmer']));

        return back()->with('success', 'Pesanan berhasil dikonfirmasi.');
    }

    public function markShipped(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeFarmerOrder($order);

        $validated = $request->validate([
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        if (!in_array($order->order_status, ['confirmed', 'processing'])) {
            return back()->with('error', 'Pesanan harus dikonfirmasi terlebih dahulu sebelum dikirim.');
        }

        if ($order->payment_status !== 'paid') {
            return back()->with('error', 'Pembayaran belum diterima. Jangan kirim pesanan sebelum pembayaran lunas.');
        }

        $order->update([
            'order_status'    => 'shipped',
            'tracking_number' => $validated['tracking_number'],
            'shipped_at'      => now(),
        ]);

        $this->orderNotifications->orderShipped($order->fresh(['buyer', 'farmer']));

        return back()->with('success', 'Pesanan berhasil ditandai sebagai dikirim.');
    }

    private function authorizeFarmerOrder(Order $order): void
    {
        if ($order->farmer_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Pesanan ini bukan milik Anda.');
        }
    }
}
