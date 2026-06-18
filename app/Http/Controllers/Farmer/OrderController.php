<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with('buyer')
            ->where('farmer_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('farmer.orders.index', compact('orders'));
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

        $order->update(['order_status' => 'confirmed']);

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

        $order->update([
            'order_status'    => 'shipped',
            'tracking_number' => $validated['tracking_number'],
            'shipped_at'      => now(),
        ]);

        return back()->with('success', 'Pesanan berhasil ditandai sebagai dikirim.');
    }

    private function authorizeFarmerOrder(Order $order): void
    {
        if ($order->farmer_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Pesanan ini bukan milik Anda.');
        }
    }
}
