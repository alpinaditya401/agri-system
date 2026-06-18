<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommodityPrice;
use App\Models\FertilizerTransaction;
use App\Models\Order;
use App\Services\FertilizerQuotaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly FertilizerQuotaService $quotaService)
    {
    }

    /**
     * Fertilizer distribution report.
     */
    public function fertilizerDistribution(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);
        $report = $this->quotaService->getStockMovementReport($year);

        return view('admin.reports.fertilizer', compact('report', 'year'));
    }

    /**
     * E-commerce transactions report.
     */
    public function transactions(Request $request): View
    {
        $orders = Order::with(['buyer', 'farmer'])
            ->when($request->query('status'), fn($q, $status) => $q->where('order_status', $status))
            ->when($request->query('from'), fn($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(25);

        $summary = [
            'total_orders'    => Order::count(),
            'total_revenue'   => Order::where('payment_status', 'paid')->sum('total_amount'),
            'pending_orders'  => Order::where('order_status', 'pending')->count(),
            'completed_orders'=> Order::where('order_status', 'completed')->count(),
        ];

        return view('admin.reports.transactions', compact('orders', 'summary'));
    }

    /**
     * Commodity prices report.
     */
    public function commodityPrices(Request $request): View
    {
        $prices = CommodityPrice::orderBy('price_date', 'desc')
            ->when($request->query('category'), fn($q, $cat) => $q->where('category', $cat))
            ->latest('price_date')
            ->paginate(25);

        $categories = CommodityPrice::select('category')->distinct()->pluck('category');

        return view('admin.reports.prices', compact('prices', 'categories'));
    }
}
