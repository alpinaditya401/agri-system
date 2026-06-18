<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $farmer = Auth::user();

        $stats = [
            'total_products'   => Product::where('farmer_id', $farmer->id)->count(),
            'active_products'  => Product::where('farmer_id', $farmer->id)->where('status', 'active')->count(),
            'incoming_orders'  => Order::where('farmer_id', $farmer->id)
                                       ->whereIn('order_status', ['pending', 'confirmed', 'processing'])
                                       ->count(),
            'total_revenue'    => Order::where('farmer_id', $farmer->id)
                                       ->where('payment_status', 'paid')
                                       ->sum('total_amount'),
        ];

        $recentOrders = Order::with('buyer')
            ->where('farmer_id', $farmer->id)
            ->latest()
            ->limit(5)
            ->get();

        $quota = \App\Models\FertilizerQuota::where('farmer_id', $farmer->id)->first();
        $products = Product::where('farmer_id', $farmer->id)->latest()->limit(8)->get();
        $commodityPrices = \App\Models\CommodityPrice::latest('price_date')->limit(10)->get();

        return view('farmer.dashboard', compact('stats', 'recentOrders', 'quota', 'products', 'commodityPrices'));
    }
}
