<?php

use Illuminate\Support\Facades\Route;

// ── Controllers ──────────────────────────────────────────────────────────────
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;

use App\Http\Controllers\PublicController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\FarmerVerificationController;
use App\Http\Controllers\Admin\FertilizerQuotaAdminController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\ReportController;

use App\Http\Controllers\Farmer\DashboardController as FarmerDashboard;
use App\Http\Controllers\Farmer\ProductController as FarmerProductController;
use App\Http\Controllers\Farmer\OrderController as FarmerOrderController;
use App\Http\Controllers\Farmer\FertilizerController as FarmerFertilizerController;

use App\Http\Controllers\Buyer\DashboardController as BuyerDashboard;
use App\Http\Controllers\Buyer\ProductController as BuyerProductController;
use App\Http\Controllers\Buyer\CartController;
use App\Http\Controllers\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Buyer\FarmerRegistrationController as BuyerFarmerRegistrationController;

use App\Http\Controllers\Distributor\DashboardController as DistributorDashboard;
use App\Http\Controllers\Distributor\StockController;
use App\Http\Controllers\Distributor\FertilizerTransactionController;

use App\Http\Controllers\Api\MapGeoJsonController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\FertilizerTrackingController;

// =============================================================================
// PUBLIC ROUTES
// =============================================================================

Route::get('/', [PublicController::class, 'landing'])->name('home');
Route::get('/harga-komoditas', [PublicController::class, 'commodityPrices'])->name('public.prices');
Route::get('/artikel', [PublicController::class, 'articles'])->name('public.articles');
Route::get('/artikel/{slug}', [PublicController::class, 'articleShow'])->name('public.articles.show');
Route::get('/produk', [BuyerProductController::class, 'index'])->name('products.index');
Route::get('/produk/{slug}', [BuyerProductController::class, 'show'])->name('products.show');
Route::get('/peta', [PublicController::class, 'map'])->name('public.map');

// =============================================================================
// AUTH ROUTES
// =============================================================================

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::match(['get', 'post'], '/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Chat (real-time messaging — available to all authenticated roles)
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/initiate/{seller_id}', [App\Http\Controllers\ChatController::class, 'initiateChat'])->name('chat.initiate');

    // Notifications (available to all authenticated roles)
    Route::get('/notifikasi', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
});

// =============================================================================
// DASHBOARD REDIRECT (role-aware)
// =============================================================================

Route::middleware('auth')->get('/dashboard', function () {
    return redirect()->route(match(auth()->user()->role->name) {
        'admin_master'=> 'admin-master.dashboard',
        'admin'       => 'admin.dashboard',
        'farmer'      => 'farmer.dashboard',
        'buyer'       => 'buyer.dashboard',
        'distributor' => 'distributor.dashboard',
        default       => 'home',
    });
})->name('dashboard');

// =============================================================================
// ADMIN ROUTES
// =============================================================================

Route::middleware(['auth', 'role:admin_master'])
     ->prefix('admin-master')
     ->name('admin-master.')
     ->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/payment-settings', [PaymentSettingsController::class, 'edit'])->name('payment-settings.edit');
    Route::patch('/payment-settings', [PaymentSettingsController::class, 'update'])->name('payment-settings.update');
});

Route::middleware(['auth', 'role:admin,admin_master'])
     ->prefix('admin')
     ->name('admin.')
     ->group(function () {

    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // User management
    Route::resource('users', UserManagementController::class);
    Route::patch('users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])
         ->name('users.toggle-active');

    // Farmer verification
    Route::get('verifikasi-petani', [FarmerVerificationController::class, 'index'])->name('farmers.verify.index');
    Route::patch('verifikasi-petani/{farmer}/approve', [FarmerVerificationController::class, 'approve'])->name('farmers.verify.approve');
    Route::patch('verifikasi-petani/{farmer}/reject', [FarmerVerificationController::class, 'reject'])->name('farmers.verify.reject');

    // Fertilizer quota management
    Route::get('kuota-pupuk', [FertilizerQuotaAdminController::class, 'index'])->name('fertilizer.quota.index');
    Route::post('kuota-pupuk/alokasi', [FertilizerQuotaAdminController::class, 'allocate'])->name('fertilizer.quota.allocate');
    Route::post('kuota-pupuk/bulk-alokasi', [FertilizerQuotaAdminController::class, 'bulkAllocate'])->name('fertilizer.quota.bulk');
    Route::get('kuota-pupuk/laporan', [FertilizerQuotaAdminController::class, 'report'])->name('fertilizer.quota.report');

    // Stok movement report
    Route::get('laporan/distribusi-pupuk', [ReportController::class, 'fertilizerDistribution'])->name('reports.fertilizer');
    Route::get('laporan/distribusi-pupuk/export', [ReportController::class, 'exportFertilizerDistribution'])->name('reports.fertilizer.export');
    Route::get('laporan/transaksi', [ReportController::class, 'transactions'])->name('reports.transactions');
    Route::get('laporan/transaksi/export', [ReportController::class, 'exportTransactions'])->name('reports.transactions.export');
    Route::get('laporan/harga-komoditas', [ReportController::class, 'commodityPrices'])->name('reports.prices');
    Route::get('laporan/harga-komoditas/export', [ReportController::class, 'exportCommodityPrices'])->name('reports.prices.export');

    // Articles management
    Route::resource('artikel', \App\Http\Controllers\Admin\ArticleController::class);
});

// =============================================================================
// FARMER ROUTES
// =============================================================================

Route::middleware(['auth', 'role:farmer'])
     ->prefix('petani')
     ->name('farmer.')
     ->group(function () {

    Route::get('/dashboard', [FarmerDashboard::class, 'index'])->name('dashboard');

    // Product management
    Route::resource('produk', FarmerProductController::class);
    Route::patch('produk/{product}/toggle-status', [FarmerProductController::class, 'toggleStatus'])
         ->name('produk.toggle-status');

    // Orders (incoming from buyers)
    Route::get('pesanan', [FarmerOrderController::class, 'index'])->name('orders.index');
    Route::get('pesanan/{order}', [FarmerOrderController::class, 'show'])->name('orders.show');
    Route::patch('pesanan/{order}/konfirmasi', [FarmerOrderController::class, 'confirm'])->name('orders.confirm');
    Route::patch('pesanan/{order}/kirim', [FarmerOrderController::class, 'markShipped'])->name('orders.ship');

    // Subsidized fertilizer
    Route::middleware('farmer.verified')->group(function () {
        Route::get('pupuk-subsidi', [FarmerFertilizerController::class, 'index'])->name('fertilizer.index');
        Route::get('pupuk-subsidi/{type}/ajukan', [FarmerFertilizerController::class, 'create'])->name('fertilizer.create');
        Route::post('pupuk-subsidi', [FarmerFertilizerController::class, 'store'])->name('fertilizer.store');
        Route::get('pupuk-subsidi/riwayat', [FarmerFertilizerController::class, 'history'])->name('fertilizer.history');
        Route::get('pupuk-subsidi/transaksi/{transaction}', [FarmerFertilizerController::class, 'showTransaction'])
             ->name('fertilizer.transactions.show');
        Route::patch('pupuk-subsidi/transaksi/{transaction}/batal', [FarmerFertilizerController::class, 'cancel'])
             ->name('fertilizer.transactions.cancel');
    });
});

// =============================================================================
// BUYER ROUTES
// =============================================================================

Route::middleware(['auth', 'role:buyer'])
     ->prefix('pembeli')
     ->name('buyer.')
     ->group(function () {

    Route::get('/dashboard', [BuyerDashboard::class, 'index'])->name('dashboard');
    Route::get('daftar-penjual', [BuyerFarmerRegistrationController::class, 'create'])->name('become-farmer.create');
    Route::post('daftar-penjual', [BuyerFarmerRegistrationController::class, 'store'])->name('become-farmer.store');

    // Cart
    Route::get('keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('keranjang', [CartController::class, 'add'])->name('cart.add');
    Route::patch('keranjang/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('keranjang/{cart}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('keranjang/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    // Orders
    Route::get('pesanan', [BuyerOrderController::class, 'index'])->name('orders.index');
    Route::get('pesanan/{order}', [BuyerOrderController::class, 'show'])->name('orders.show');
    Route::post('pesanan/{order}/bayar', [BuyerOrderController::class, 'pay'])->name('orders.pay');
    Route::patch('pesanan/{order}/selesai', [BuyerOrderController::class, 'complete'])->name('orders.complete');
    Route::patch('pesanan/{order}/batal', [BuyerOrderController::class, 'cancel'])->name('orders.cancel');
});

// =============================================================================
// DISTRIBUTOR ROUTES
// =============================================================================

Route::middleware(['auth', 'role:distributor'])
     ->prefix('distributor')
     ->name('distributor.')
     ->group(function () {

    Route::get('/dashboard', [DistributorDashboard::class, 'index'])->name('dashboard');

    // Fertilizer stock management
    Route::get('stok', [StockController::class, 'index'])->name('stock.index');
    Route::post('stok/tambah', [StockController::class, 'addStock'])->name('stock.add');
    Route::get('stok/riwayat', [StockController::class, 'history'])->name('stock.history');

    // Fertilizer transaction processing
    Route::get('transaksi-pupuk', [FertilizerTransactionController::class, 'index'])->name('fertilizer.index');
    Route::get('transaksi-pupuk/{transaction}', [FertilizerTransactionController::class, 'show'])->name('fertilizer.show');
    Route::patch('transaksi-pupuk/{transaction}/setujui', [FertilizerTransactionController::class, 'approve'])->name('fertilizer.approve');
    Route::patch('transaksi-pupuk/{transaction}/tolak', [FertilizerTransactionController::class, 'reject'])->name('fertilizer.reject');
    Route::patch('transaksi-pupuk/{transaction}/serahkan', [FertilizerTransactionController::class, 'dispense'])->name('fertilizer.dispense');
});

// =============================================================================
// PUBLIC API ROUTES (for map / frontend consumption)
// =============================================================================

Route::prefix('api')->name('api.')->group(function () {
    // GeoJSON endpoints (public — no auth required)
    Route::get('/map/farmers', [MapGeoJsonController::class, 'farmers'])->name('map.farmers');
    Route::get('/map/distributors', [MapGeoJsonController::class, 'distributors'])->name('map.distributors');
    Route::get('/map/products', [MapGeoJsonController::class, 'products'])->name('map.products');
    Route::get('/map/combined', [MapGeoJsonController::class, 'combined'])->name('map.combined');

    // Payment webhook (no CSRF — signed by payment gateway)
    Route::post('/payment/webhook', [PaymentWebhookController::class, 'handle'])
         ->name('payment.webhook')
         ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});

// =============================================================================
// CHAT & NOTIFICATION AJAX ROUTES (web session auth, used by all roles)
// =============================================================================

Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::get('/chat/contacts', [\App\Http\Controllers\ChatController::class, 'contacts'])->name('chat.contacts');
    Route::get('/chat/contacts/search', [\App\Http\Controllers\ChatController::class, 'availableContacts'])->name('chat.contacts.search');
    Route::post('/chat/contacts', [\App\Http\Controllers\ChatController::class, 'storeContact'])->name('chat.contacts.store');
    Route::patch('/chat/contacts/{contact}', [\App\Http\Controllers\ChatController::class, 'updateContact'])->name('chat.contacts.update');
    Route::delete('/chat/contacts/{contact}', [\App\Http\Controllers\ChatController::class, 'destroyContact'])->name('chat.contacts.destroy');
    Route::get('/chat/messages', [\App\Http\Controllers\ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/send', [\App\Http\Controllers\ChatController::class, 'send'])->name('chat.send');

    Route::get('/fertilizer-transactions/{transaction}/tracking', [FertilizerTrackingController::class, 'show'])->name('fertilizer-tracking.show');
    Route::patch('/fertilizer-transactions/{transaction}/tracking', [FertilizerTrackingController::class, 'update'])->name('fertilizer-tracking.update');

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::get('/notifications/summary', [\App\Http\Controllers\NotificationController::class, 'summary'])->name('notifications.summary');
    Route::patch('/notifications/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/stock-alerts', [\App\Http\Controllers\NotificationController::class, 'stockAlerts'])->name('notifications.stock-alerts');
});

// =============================================================================
// AUTHENTICATED API ROUTES (for AJAX/SPA consumption)
// =============================================================================

Route::middleware('auth:sanctum')
     ->prefix('api')
     ->name('api.')
     ->group(function () {
    Route::get('/commodity-prices', [\App\Http\Controllers\Api\CommodityPriceController::class, 'index']);
    Route::get('/my-quota', [\App\Http\Controllers\Api\QuotaController::class, 'mine']);
});
