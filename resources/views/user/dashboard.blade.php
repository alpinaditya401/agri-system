<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pembeli - Agrilink</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    <div class="md:hidden fixed top-0 left-0 w-full h-16 bg-emerald-900 flex items-center justify-between px-4 z-50 shadow-md">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto">
            <span class="font-bold text-white text-lg">Agrilink</span>
        </div>
        <button id="mobileMenuBtn" class="text-white p-2" aria-label="Buka menu">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-emerald-900 flex flex-col flex-shrink-0 h-screen overflow-y-auto transform -translate-x-full md:relative md:translate-x-0 transition-all duration-300">
        <button id="desktopToggleBtn" class="hidden md:flex absolute top-5 right-4 bg-emerald-500 rounded-full w-8 h-8 items-center justify-center text-white cursor-pointer z-50 shadow-lg hover:bg-emerald-400 transition-colors" aria-label="Kecilkan sidebar">
            <svg class="w-5 h-5 transition-transform duration-300" id="desktopToggleIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>

        <div class="flex items-center gap-2 px-4 py-5 border-b border-emerald-700/50">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto flex-shrink-0">
            <span class="sidebar-text opacity-100 font-bold text-white text-base whitespace-nowrap overflow-hidden transition-all duration-300">Agrilink</span>
            <button id="mobileCloseBtn" class="md:hidden ml-auto text-emerald-200 hover:text-white p-1" aria-label="Tutup menu">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 overflow-x-hidden">
            <a href="{{ route('buyer.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-white bg-emerald-700 text-sm font-medium" title="Dashboard">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Dashboard</span>
            </a>
            <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100 hover:bg-emerald-700/50 text-sm font-medium transition-all" title="Belanja Produk">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Belanja Produk</span>
            </a>
            <a href="{{ route('buyer.cart.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100 hover:bg-emerald-700/50 text-sm font-medium transition-all" title="Keranjang">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Keranjang</span>
            </a>
            <a href="{{ route('buyer.orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100 hover:bg-emerald-700/50 text-sm font-medium transition-all" title="Pesanan">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Pesanan</span>
            </a>
            <a href="{{ route('public.prices') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100 hover:bg-emerald-700/50 text-sm font-medium transition-all" title="Info Harga">
                <img src="{{ asset('images/icon4.png') }}" alt="Info Harga" class="w-5 h-5 flex-shrink-0 object-contain">
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Info Harga</span>
            </a>
            <a href="{{ route('public.map') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100 hover:bg-emerald-700/50 text-sm font-medium transition-all" title="Peta">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Peta Distribusi</span>
            </a>
        </nav>

        <div class="mt-auto border-t border-emerald-700/50 p-4">
            <div class="flex items-start gap-3">
                <a href="{{ route('profile.edit') }}" class="w-10 h-10 flex-shrink-0 bg-emerald-400 rounded-full flex items-center justify-center text-white font-bold text-lg hover:bg-emerald-300 transition-colors shadow-sm" title="Buka Profil">
                    {{ substr(auth()->user()->name ?? 'P', 0, 1) }}
                </a>
                <div class="sidebar-text opacity-100 whitespace-nowrap transition-all duration-300 overflow-hidden flex-1">
                    <a href="{{ route('profile.edit') }}" class="block hover:opacity-80 transition-opacity" title="Buka Profil">
                        <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->name ?? 'Pembeli' }}</p>
                    </a>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-emerald-300 text-xs truncate">Pembeli</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-red-300 hover:text-red-100 text-xs font-medium flex items-center gap-1 transition-colors bg-red-900/30 hover:bg-red-900/50 px-2 py-1 rounded-md" title="Keluar">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto p-5 pt-20 md:pt-5 w-full">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-5">
            <div>
                <h1 class="text-lg font-bold text-gray-800">Selamat Datang, {{ auth()->user()->name ?? 'Pembeli' }} (Pembeli)</h1>
                <p class="text-gray-500 text-sm">Belanja hasil tani, pantau pesanan, cek keranjang, dan lihat harga komoditas terbaru.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('products.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">Belanja Sekarang</a>
                <a href="{{ route('buyer.cart.index') }}" class="bg-white hover:bg-emerald-50 border border-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">Buka Keranjang</a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-emerald-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Total Pesanan</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_orders'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">semua transaksi</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-yellow-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Menunggu</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['pending_orders'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">perlu dipantau</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Selesai</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['completed_orders'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">pesanan diterima</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-teal-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Keranjang</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['cart_items'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">item siap checkout</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">
            <div class="bg-white rounded-xl p-5 shadow-sm xl:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">Produk Tani Direkomendasikan</h2>
                    <a href="{{ route('products.index') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Lihat katalog</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($featuredProducts as $product)
                    <a href="{{ route('products.show', $product->slug) }}" class="group bg-gray-50 hover:bg-emerald-50 rounded-xl p-3 transition-colors">
                        <div class="h-28 bg-emerald-100 rounded-lg mb-3 overflow-hidden flex items-center justify-center text-3xl">
                            @if($product->main_image)
                                <img src="{{ asset($product->main_image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            @else
                                🌱
                            @endif
                        </div>
                        <p class="font-semibold text-gray-800 text-sm truncate">{{ $product->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $product->farmer->name ?? 'Petani Agrilink' }}</p>
                        <p class="text-sm text-emerald-700 font-bold mt-2">Rp {{ number_format($product->price_per_unit, 0, ',', '.') }}/{{ $product->unit }}</p>
                    </a>
                    @empty
                    <div class="md:col-span-2 lg:col-span-3 bg-gray-50 rounded-xl p-5 text-center">
                        <p class="font-semibold text-gray-700 text-sm">Belum ada produk aktif</p>
                        <p class="text-xs text-gray-500 mt-1">Produk petani akan tampil di sini setelah tersedia.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">Keranjang Saya</h2>
                    <a href="{{ route('buyer.cart.index') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Detail</a>
                </div>
                <div class="space-y-3">
                    @forelse($cartItems as $item)
                    <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3">
                        <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center text-xl overflow-hidden">
                            @if($item->product?->main_image)
                                <img src="{{ asset($item->product->main_image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                            @else
                                🛒
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 text-sm truncate">{{ $item->product->name ?? 'Produk tidak tersedia' }}</p>
                            <p class="text-xs text-gray-500">{{ $item->quantity }} {{ $item->product->unit ?? 'item' }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="font-semibold text-gray-700 text-sm">Keranjang kosong</p>
                        <p class="text-xs text-gray-500 mt-1">Tambahkan produk dari katalog untuk mulai checkout.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">Pesanan Terbaru</h2>
                    <a href="{{ route('buyer.orders.index') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Kelola semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="p-3 rounded-l-lg font-semibold">Nomor</th>
                                <th class="p-3 font-semibold">Petani</th>
                                <th class="p-3 font-semibold">Total</th>
                                <th class="p-3 rounded-r-lg font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-100">
                            @forelse($recentOrders as $order)
                            <tr>
                                <td class="p-3"><a href="{{ route('buyer.orders.show', $order) }}" class="font-medium text-emerald-700">#{{ $order->order_number }}</a></td>
                                <td class="p-3 text-gray-800">{{ $order->farmer->name ?? 'Petani' }}</td>
                                <td class="p-3 text-gray-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td class="p-3"><span class="bg-gray-100 text-gray-700 text-xs font-semibold px-2.5 py-1 rounded-md">{{ ucfirst($order->order_status) }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-5 text-center text-gray-500 text-sm italic">Belum ada pesanan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">Info Harga Komoditas</h2>
                    <a href="{{ route('public.prices') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Lihat detail</a>
                </div>
                <canvas id="priceChart" height="155"></canvas>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    @forelse($commodityPrices->take(4) as $price)
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="font-semibold text-gray-800 text-sm truncate">{{ $price->commodity_name }}</p>
                        <p class="text-xs text-gray-500">{{ $price->region ?? 'Nasional' }}</p>
                        <p class="text-sm text-emerald-700 font-bold mt-1">Rp {{ number_format($price->price, 0, ',', '.') }}/{{ $price->unit }}</p>
                    </div>
                    @empty
                    <div class="col-span-2 bg-gray-50 rounded-xl p-4 text-center text-sm text-gray-500">Data harga belum tersedia.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const desktopToggleBtn = document.getElementById('desktopToggleBtn');
            const desktopToggleIcon = document.getElementById('desktopToggleIcon');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileCloseBtn = document.getElementById('mobileCloseBtn');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            let isCollapsed = false;

            desktopToggleBtn.addEventListener('click', () => {
                isCollapsed = !isCollapsed;
                sidebar.classList.toggle('w-64', !isCollapsed);
                sidebar.classList.toggle('w-20', isCollapsed);
                desktopToggleIcon.classList.toggle('rotate-180', isCollapsed);
                sidebarTexts.forEach(el => {
                    el.classList.toggle('opacity-0', isCollapsed);
                    el.classList.toggle('w-0', isCollapsed);
                    el.classList.toggle('opacity-100', !isCollapsed);
                });
            });

            function toggleMobileSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            }

            mobileMenuBtn.addEventListener('click', toggleMobileSidebar);
            mobileCloseBtn.addEventListener('click', toggleMobileSidebar);
            sidebarOverlay.addEventListener('click', toggleMobileSidebar);
        });

        new Chart(document.getElementById('priceChart'), {
            type: 'line',
            data: {
                labels: {!! $commodityPrices->pluck('commodity_name')->toJson() !!},
                datasets: [{
                    label: 'Harga',
                    data: {!! $commodityPrices->pluck('price')->toJson() !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.12)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });
    </script>
</body>
</html>
