<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Services\OrderService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentGatewayService $paymentGatewayService
    )
    {
    }

    public function index(): View
    {
        $cartItems = Cart::with('product.farmer')
            ->where('buyer_id', Auth::id())
            ->get();

        $total = $cartItems->sum(fn($item) => $item->product->price_per_unit * $item->quantity);

        return view('buyer.cart.index', compact('cartItems', 'total'));
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ]);

        try {
            $this->orderService->addToCart(Auth::id(), $validated['product_id'], $validated['quantity']);
            return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        if ($cart->buyer_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Jumlah produk diperbarui.');
    }

    public function remove(Cart $cart): RedirectResponse
    {
        if ($cart->buyer_id !== Auth::id()) {
            abort(403);
        }

        $cart->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address'       => ['required', 'string'],
            'method'        => ['nullable', 'string', 'max:50'],
            'notes'         => ['nullable', 'string'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (!$this->paymentGatewayService->isConfigured()) {
            return back()
                ->withInput()
                ->withErrors(['checkout' => 'Payment gateway belum dikonfigurasi. Pilih mode Demo Auto-Paid atau isi Midtrans Server Key di menu Admin Master > Payment Gateway.']);
        }

        try {
            $order = $this->orderService->checkoutFromCart(Auth::id(), [
                'address'       => $validated['address'],
                'method'        => $validated['method'] ?? null,
                'notes'         => $validated['notes'] ?? null,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
            ]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['checkout' => $e->getMessage()]);
        }

        try {
            $order = $this->paymentGatewayService->createPayment($order);

            if ($order->payment_checkout_url) {
                return redirect()->away($order->payment_checkout_url);
            }

            return redirect()->route('buyer.orders.show', $order)
                ->with('success', 'Checkout berhasil. Pembayaran otomatis diset paid dalam mode demo.');
        } catch (\RuntimeException $e) {
            return redirect()->route('buyer.orders.show', $order)
                ->with('error', 'Pesanan dibuat, tetapi halaman pembayaran belum bisa dibuka: ' . $e->getMessage());
        }
    }
}
