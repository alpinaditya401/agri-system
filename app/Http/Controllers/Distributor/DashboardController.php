<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\FertilizerStock;
use App\Models\FertilizerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $distributor = Auth::user();

        $stats = [
            'total_stock_kg'    => FertilizerStock::where('distributor_id', $distributor->id)->sum('stock_kg'),
            'total_reserved_kg' => FertilizerStock::where('distributor_id', $distributor->id)->sum('reserved_kg'),
            'pending_requests'  => FertilizerTransaction::where('distributor_id', $distributor->id)
                                                       ->where('status', 'pending')
                                                       ->count(),
            'approved_requests' => FertilizerTransaction::where('distributor_id', $distributor->id)
                                                       ->where('status', 'approved')
                                                       ->count(),
            'dispensed_total'   => FertilizerTransaction::where('distributor_id', $distributor->id)
                                                       ->where('status', 'dispensed')
                                                       ->count(),
        ];

        $recentTransactions = FertilizerTransaction::with(['farmer', 'fertilizerType'])
            ->where('distributor_id', $distributor->id)
            ->latest()
            ->limit(5)
            ->get();

        $stockBreakdown = FertilizerStock::with('fertilizerType')
            ->where('distributor_id', $distributor->id)
            ->latest('received_date')
            ->limit(6)
            ->get();

        return view('distributor.dashboard', compact('stats', 'recentTransactions', 'stockBreakdown'));
    }
}
