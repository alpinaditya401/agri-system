<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CommodityPrice;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $buyer = Auth::user();

        $stats = [
            'total_orders'      => Order::where('buyer_id', $buyer->id)->count(),
            'pending_orders'    => Order::where('buyer_id', $buyer->id)->where('order_status', 'pending')->count(),
            'completed_orders'  => Order::where('buyer_id', $buyer->id)->where('order_status', 'completed')->count(),
            'total_spent'       => Order::where('buyer_id', $buyer->id)->where('payment_status', 'paid')->sum('total_amount'),
            'cart_items'        => Cart::where('buyer_id', $buyer->id)->count(),
        ];

        $recentOrders = Order::with('farmer')
            ->where('buyer_id', $buyer->id)
            ->latest()
            ->limit(5)
            ->get();

        $cartItems = Cart::with('product')
            ->where('buyer_id', $buyer->id)
            ->latest()
            ->limit(4)
            ->get();

        $featuredProducts = Product::with('farmer', 'category')
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->latest()
            ->limit(6)
            ->get();

        $commodityPrices = CommodityPrice::latest('price_date')->limit(8)->get();

        return view('user.dashboard', compact('stats', 'recentOrders', 'cartItems', 'featuredProducts', 'commodityPrices'));
    }
}
