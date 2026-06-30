<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\FertilizerTransaction;
use App\Models\Article;
use App\Models\FertilizerType;
use App\Support\DashboardRegion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (auth()->user()?->isAdminMaster() && request()->routeIs('admin.dashboard')) {
            return redirect()->route('admin-master.dashboard');
        }

        $regionFilters = DashboardRegion::fromRequest($request);
        $regionOptions = DashboardRegion::options($regionFilters);

        $usersForRegion = fn() => DashboardRegion::applyUser(User::query(), $regionFilters);
        $ordersForRegion = fn() => DashboardRegion::applyRelatedUser(Order::query(), 'farmer', $regionFilters);
        $fertilizerForRegion = fn() => DashboardRegion::applyRelatedUser(FertilizerTransaction::query(), 'farmer', $regionFilters);

        $stats = [
            'total_users'        => $usersForRegion()->count(),
            'total_farmers'      => $usersForRegion()->whereHas('role', fn($q) => $q->where('name', 'farmer'))->count(),
            'total_buyers'       => $usersForRegion()->whereHas('role', fn($q) => $q->where('name', 'buyer'))->count(),
            'total_distributors' => $usersForRegion()->whereHas('role', fn($q) => $q->where('name', 'distributor'))->count(),
            'active_subsidy_distributors' => $usersForRegion()
                ->where('is_active', true)
                ->whereHas('role', fn($q) => $q->where('name', 'distributor'))
                ->whereHas('distributorProfile', fn($q) => $q->where('verification_status', 'verified'))
                ->count(),
            'pending_verifications' => $usersForRegion()->whereHas('farmerProfile', fn($q) => $q->where('verification_status', 'pending'))->count(),
            'pending_distributor_verifications' => $usersForRegion()->whereHas('distributorProfile', fn($q) => $q->where('verification_status', 'pending'))->count(),
            'total_orders'       => $ordersForRegion()->count(),
            'pending_orders'     => $ordersForRegion()->where('order_status', 'pending')->count(),
            'fertilizer_transactions' => $fertilizerForRegion()->where('status', 'pending')->count(),
            'total_articles'     => Article::count(),
        ];

        $fertilizerInventory = FertilizerType::withSum([
            'stocks' => fn($query) => DashboardRegion::applyRelatedUser($query, 'distributor', $regionFilters),
        ], 'stock_kg')->get();

        $latestArticles = Article::latest()->limit(4)->get();

        $pendingFarmers = $usersForRegion()->with('farmerProfile')
            ->whereHas('farmerProfile', fn($q) => $q->where('verification_status', 'pending'))
            ->latest()
            ->limit(5)
            ->get();

        $pendingDistributors = $usersForRegion()->with('distributorProfile')
            ->whereHas('distributorProfile', fn($q) => $q->where('verification_status', 'pending'))
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'fertilizerInventory', 'latestArticles', 'pendingFarmers', 'pendingDistributors', 'regionFilters', 'regionOptions'));
    }
}
