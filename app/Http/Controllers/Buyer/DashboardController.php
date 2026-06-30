<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
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
        $buyer = Auth::user();
        $regionFilters = DashboardRegion::fromRequest($request);
        $regionOptions = DashboardRegion::options($regionFilters);

        $ordersForRegion = fn() => DashboardRegion::applyRelatedUser(
            Order::where('buyer_id', $buyer->id),
            'farmer',
            $regionFilters
        );

        $cartForRegion = function () use ($buyer, $regionFilters) {
            $query = Cart::with('product.farmer')
                ->where('buyer_id', $buyer->id);

            if (DashboardRegion::hasFilter($regionFilters)) {
                $query->whereHas('product', fn($productQuery) => DashboardRegion::applyProduct($productQuery, $regionFilters));
            }

            return $query;
        };

        $cartItems = $cartForRegion()
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total_orders' => $ordersForRegion()->count(),
            'pending_orders' => $ordersForRegion()->where('order_status', 'pending')->count(),
            'completed_orders' => $ordersForRegion()->where('order_status', 'completed')->count(),
            'total_spent' => $ordersForRegion()->where('payment_status', 'paid')->sum('total_amount'),
            'cart_items' => $cartForRegion()->count(),
        ];

        $recentOrders = $ordersForRegion()->with('farmer')
            ->latest()
            ->limit(5)
            ->get();

        $featuredProductsQuery = Product::with('farmer', 'category')
            ->active()
            ->where('stock_quantity', '>', 0);

        DashboardRegion::applyProduct($featuredProductsQuery, $regionFilters);

        $featuredProducts = $featuredProductsQuery
            ->latest()
            ->limit(6)
            ->get();

        $commodityPrices = DashboardRegion::filterCommodityPrices(collect($this->bpsApiService->getLatestPrices(8)), $regionFilters);
        $productReferencePrices = $this->bpsApiService->getProductReferencePrices($featuredProducts);

        return view('user.dashboard', compact('stats', 'recentOrders', 'commodityPrices', 'featuredProducts', 'cartItems', 'productReferencePrices', 'regionFilters', 'regionOptions'));
    }
}