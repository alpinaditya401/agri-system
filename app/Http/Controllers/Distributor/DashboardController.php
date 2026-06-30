<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\FertilizerStock;
use App\Models\FertilizerTransaction;
use App\Support\DashboardRegion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $distributor = Auth::user();
        $regionFilters = DashboardRegion::fromRequest($request);
        $regionOptions = DashboardRegion::options($regionFilters);

        $transactionsForRegion = fn() => DashboardRegion::applyRelatedUser(
            FertilizerTransaction::where('distributor_id', $distributor->id),
            'farmer',
            $regionFilters
        );

        $stats = [
            'total_stock_kg'    => FertilizerStock::where('distributor_id', $distributor->id)->sum('stock_kg'),
            'total_reserved_kg' => FertilizerStock::where('distributor_id', $distributor->id)->sum('reserved_kg'),
            'pending_requests'  => $transactionsForRegion()
                                                       ->where('status', 'pending')
                                                       ->count(),
            'approved_requests' => $transactionsForRegion()
                                                       ->where('status', 'approved')
                                                       ->count(),
            'dispensed_total'   => $transactionsForRegion()
                                                       ->where('status', 'dispensed')
                                                       ->count(),
        ];

        $recentTransactions = $transactionsForRegion()->with(['farmer', 'fertilizerType'])
            ->latest()
            ->limit(5)
            ->get();

        $stockBreakdown = FertilizerStock::with('fertilizerType')
            ->where('distributor_id', $distributor->id)
            ->latest()
            ->limit(6)
            ->get();

        return view('distributor.dashboard', compact('stats', 'recentTransactions', 'stockBreakdown', 'regionFilters', 'regionOptions'));
    }
}
