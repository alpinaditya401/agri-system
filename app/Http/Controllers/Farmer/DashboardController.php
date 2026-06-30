<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\BpsApiService;
use App\Support\DashboardRegion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly BpsApiService $bpsApiService)
    {
    }

    public function index(Request $request): View
    {
        $farmer = Auth::user();
        $regionFilters = DashboardRegion::fromRequest($request);
        $regionOptions = DashboardRegion::options($regionFilters);

        $ordersForRegion = fn() => DashboardRegion::applyRelatedUser(
            Order::where('farmer_id', $farmer->id),
            'buyer',
            $regionFilters
        );

        $stats = [
            'total_products'   => Product::where('farmer_id', $farmer->id)->count(),
            'active_products'  => Product::where('farmer_id', $farmer->id)->where('status', 'active')->count(),
            'incoming_orders'  => $ordersForRegion()
                                       ->whereIn('order_status', ['pending', 'confirmed', 'processing'])
                                       ->count(),
            'total_revenue'    => $ordersForRegion()
                                       ->where('payment_status', 'paid')
                                       ->sum('total_amount'),
        ];

        $recentOrders = $ordersForRegion()->with('buyer')
            ->latest()
            ->limit(5)
            ->get();

        $quota = \App\Models\FertilizerQuota::where('farmer_id', $farmer->id)->first();
        $products = Product::with('category')->where('farmer_id', $farmer->id)->latest()->limit(8)->get();
        $commodityPrices = DashboardRegion::filterCommodityPrices(collect($this->bpsApiService->getLatestPrices(10)), $regionFilters);

        $productReferencePrices = $this->bpsApiService->getProductReferencePrices($products);

        return view('farmer.dashboard', compact('stats', 'recentOrders', 'quota', 'products', 'commodityPrices', 'productReferencePrices', 'regionFilters', 'regionOptions'));
    }
}

