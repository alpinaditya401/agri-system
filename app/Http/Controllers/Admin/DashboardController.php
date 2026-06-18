<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\FertilizerTransaction;
use App\Models\Article;
use App\Models\FertilizerType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users'        => User::count(),
            'total_farmers'      => User::whereHas('role', fn($q) => $q->where('name', 'farmer'))->count(),
            'total_buyers'       => User::whereHas('role', fn($q) => $q->where('name', 'buyer'))->count(),
            'total_distributors' => User::whereHas('role', fn($q) => $q->where('name', 'distributor'))->count(),
            'pending_verifications' => User::whereHas('farmerProfile', fn($q) => $q->where('verification_status', 'pending'))->count(),
            'total_orders'       => Order::count(),
            'pending_orders'     => Order::where('order_status', 'pending')->count(),
            'fertilizer_transactions' => FertilizerTransaction::where('status', 'pending')->count(),
            'total_articles'     => Article::count(),
        ];

        $fertilizerInventory = FertilizerType::withSum('stocks', 'stock_kg')->get();
        $latestArticles = Article::latest()->limit(3)->get();
        $pendingFarmers = User::with('farmerProfile')
            ->whereHas('farmerProfile', fn($q) => $q->where('verification_status', 'pending'))
            ->latest()
            ->limit(4)
            ->get();

        return view('admin.dashboard', compact('stats', 'fertilizerInventory', 'latestArticles', 'pendingFarmers'));
    }
}
