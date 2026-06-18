<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petani – Agrilink</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    <!-- MOBILE TOPBAR -->
    <div class="md:hidden fixed top-0 left-0 w-full h-16 bg-emerald-900 flex items-center justify-between px-4 z-50 shadow-md">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto">
            <span class="font-bold text-white text-lg">Agrilink</span>
        </div>
        <button id="mobileMenuBtn" class="text-white p-2">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <!-- OVERLAY FOR MOBILE -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-emerald-900 flex flex-col flex-shrink-0 h-screen overflow-y-auto transform -translate-x-full md:relative md:translate-x-0 transition-all duration-300">
        
        <!-- Desktop Toggle Button -->
        <button id="desktopToggleBtn" class="hidden md:flex absolute top-5 right-4 bg-emerald-500 rounded-full w-8 h-8 items-center justify-center text-white cursor-pointer z-50 shadow-lg hover:bg-emerald-400 transition-colors">
            <svg class="w-5 h-5 transition-transform duration-300" id="desktopToggleIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>

        <!-- Logo -->
        <div class="flex items-center gap-2 px-4 py-5 border-b border-emerald-700/50">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto flex-shrink-0">
            <span class="sidebar-text opacity-100 font-bold text-white text-base whitespace-nowrap overflow-hidden transition-all duration-300">Agrilink</span>
            <button id="mobileCloseBtn" class="md:hidden ml-auto text-emerald-200 hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-x-hidden">
            <a href="{{ route('farmer.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-white bg-emerald-700 text-sm font-medium cursor-pointer" title="Dashboard">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Dashboard</span>
            </a>
            <a href="{{ route('farmer.produk.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100 hover:bg-emerald-700/50 text-sm font-medium cursor-pointer transition-all" title="Produk Saya">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Produk Saya</span>
            </a>
            <a href="{{ route('farmer.orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100 hover:bg-emerald-700/50 text-sm font-medium cursor-pointer transition-all" title="Pesanan Masuk">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Pesanan Masuk</span>
            </a>
            <a href="{{ route('farmer.fertilizer.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100 hover:bg-emerald-700/50 text-sm font-medium cursor-pointer transition-all" title="Pengajuan Subsidi">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Pengajuan Subsidi</span>
            </a>
        </nav>

        <!-- User Badge -->
        <div class="mt-auto border-t border-emerald-700/50 p-4">
            <div class="flex items-start gap-3">
                <a href="{{ route('profile.edit') }}" class="w-10 h-10 flex-shrink-0 bg-emerald-400 rounded-full flex items-center justify-center text-white font-bold text-lg hover:bg-emerald-300 transition-colors shadow-sm" title="Buka Profile">
                    {{ substr(auth()->user()->name ?? 'P', 0, 1) }}
                </a>
                <div class="sidebar-text opacity-100 whitespace-nowrap transition-all duration-300 overflow-hidden flex-1">
                    <a href="{{ route('profile.edit') }}" class="block hover:opacity-80 transition-opacity" title="Buka Profile">
                        <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->name ?? 'Petani' }}</p>
                    </a>
                    <div class="flex items-center justify-between mt-1">
                        <a href="{{ route('profile.edit') }}" class="text-emerald-300 text-xs truncate hover:text-emerald-100 transition-colors" title="Buka Profile">Petani</a>
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

    <!-- MAIN CONTENT -->
    <main class="flex-1 overflow-y-auto p-5 pt-20 md:pt-5 w-full">

        <!-- Welcome Header -->
        <div class="mb-5">
            <h1 class="text-lg font-bold text-gray-800">
                Selamat Datang, {{ auth()->user()->name ?? 'Petani' }} (Petani)
            </h1>
            <p class="text-gray-500 text-sm">Kelola produk, proses pesanan pembeli, pantau harga komoditas, dan ajukan pupuk bersubsidi.</p>
        </div>

        <!-- QUICK ACCESS BUTTONS -->
        <div class="flex flex-wrap gap-3 mb-5">
            <a href="{{ route('public.prices') }}" class="flex items-center gap-2 bg-white hover:bg-emerald-50 border border-gray-200 hover:border-emerald-300 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">
                📊 Info Harga
            </a>
            <a href="{{ route('farmer.produk.create') }}" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 border border-emerald-600 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">
                + Tambah Produk
            </a>
            <a href="{{ route('farmer.fertilizer.index') }}" class="flex items-center gap-2 bg-white hover:bg-emerald-50 border border-gray-200 hover:border-emerald-300 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">
                🌿 Ajukan Pupuk
            </a>
            <a href="{{ route('public.map') }}" class="flex items-center gap-2 bg-white hover:bg-emerald-50 border border-gray-200 hover:border-emerald-300 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">
                🗺️ Monitor Distribusi
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-emerald-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Produk Aktif</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['active_products'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">dari {{ $stats['total_products'] ?? 0 }} produk</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Pesanan Masuk</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['incoming_orders'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">perlu diproses</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-yellow-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Omzet Dibayar</p>
                <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500 mt-1">transaksi lunas</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-teal-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Kuota Pupuk</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($quota ? $quota->remaining_kg : 0, 0, ',', '.') }} Kg</p>
                <p class="text-xs text-gray-500 mt-1">tersisa</p>
            </div>
        </div>

        <!-- MIDDLE GRID: Subsidi + Map -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

            <!-- Verifikasi Subsidi Card -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <h2 class="font-bold text-gray-800 mb-4">Verifikasi Subsidi</h2>
                <p class="text-xs text-gray-400 uppercase font-semibold mb-2">NIK & Kelompok Tani</p>
                <div class="relative mb-4">
                    <input type="text" placeholder="NIK & Kelompok Tani" value="{{ auth()->user()->farmerProfile->nik ?? 'NIK belum diisi' }} - {{ auth()->user()->farmerProfile->farmer_group_name ?? 'Kelompok belum diisi' }}" readonly
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-400 bg-gray-50 text-gray-500">
                    <button class="absolute right-3 top-1/2 -translate-y-1/2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>

                <!-- Kuota Tersisa Donut -->
                <h3 class="font-semibold text-gray-700 text-sm mb-3">Kuota Tersisa: {{ $quota ? $quota->remaining_kg : 0 }} Kg</h3>
                <div class="flex items-center justify-center">
                    <div class="relative w-32 h-32">
                        <canvas id="quotaChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl">🌿</span>
                            <span class="text-xs font-semibold text-gray-600">{{ $quota && $quota->allocated_kg > 0 ? round(($quota->remaining_kg / $quota->allocated_kg) * 100) : 0 }}%</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-2">
                    <span>Terpakai: {{ $quota ? $quota->used_kg : 0 }} Kg</span>
                    <span>Total: {{ $quota ? $quota->allocated_kg : 0 }} Kg</span>
                </div>
            </div>

            <!-- Location Tracking Map -->
            <div class="bg-white rounded-xl p-5 shadow-sm lg:col-span-2">
                <h2 class="font-bold text-gray-800 mb-4">Location-based tracking kios / Truck</h2>
                <div class="bg-emerald-50 border-2 border-dashed border-emerald-200 rounded-xl h-52 flex flex-col items-center justify-center">
                    <span class="text-5xl mb-2">📍</span>
                    <p class="text-emerald-700 font-semibold">Peta Distribusi Interaktif</p>
                    <p class="text-emerald-500 text-xs mt-1 text-center px-4">Lihat titik petani, distributor, dan produk aktif di peta Agrilink.</p>
                    <a href="{{ route('public.map') }}" class="mt-3 text-xs bg-emerald-600 text-white px-4 py-1.5 rounded-lg font-medium hover:bg-emerald-700 transition-colors">Buka Peta</a>
                </div>
            </div>
        </div>

        <!-- BOTTOM GRID: Chart BPS + Katalog Mini -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            <!-- Line Chart: Info Harga BPS -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">Info Harga BPS</h2>
                    <div class="flex gap-3 text-xs text-gray-500">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>Harga (Rp)</span>
                    </div>
                </div>
                <canvas id="lineChart" height="160"></canvas>
            </div>

            <!-- Katalog Mini -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <h2 class="font-bold text-gray-800 mb-4">Produk Anda</h2>
                <div class="border-t pt-4">
                    <div class="space-y-3">
                        @forelse($products as $product)
                        <div class="flex gap-3 items-center">
                            <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center text-2xl flex-shrink-0">
                                @if($product->main_image)
                                    <img src="{{ asset($product->main_image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-xl">
                                @else
                                    🌱
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">Rp {{ number_format($product->price_per_unit, 0, ',', '.') }}/{{ $product->unit }} • Stok: {{ $product->stock_quantity }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-gray-500 italic">Belum ada produk yang dijual.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm mt-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-800">Pesanan Terbaru</h2>
                <a href="{{ route('farmer.orders.index') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Kelola semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-3 rounded-l-lg font-semibold">Nomor</th>
                            <th class="p-3 font-semibold">Pembeli</th>
                            <th class="p-3 font-semibold">Total</th>
                            <th class="p-3 font-semibold">Status</th>
                            <th class="p-3 rounded-r-lg font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @forelse($recentOrders as $order)
                        <tr>
                            <td class="p-3 font-medium text-emerald-700">#{{ $order->order_number }}</td>
                            <td class="p-3 text-gray-800">{{ $order->buyer->name ?? 'Pembeli' }}</td>
                            <td class="p-3 text-gray-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="p-3"><span class="bg-gray-100 text-gray-700 text-xs font-semibold px-2.5 py-1 rounded-md">{{ ucfirst($order->order_status) }}</span></td>
                            <td class="p-3"><a href="{{ route('farmer.orders.show', $order) }}" class="text-emerald-600 hover:text-emerald-800 font-medium">Detail</a></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-5 text-center text-gray-500 text-sm italic">Belum ada pesanan masuk.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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

            // Desktop Toggle
            desktopToggleBtn.addEventListener('click', () => {
                isCollapsed = !isCollapsed;
                if (isCollapsed) {
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-20');
                    desktopToggleIcon.classList.add('rotate-180');
                    sidebarTexts.forEach(el => {
                        el.classList.remove('opacity-100');
                        el.classList.add('opacity-0', 'w-0');
                    });
                } else {
                    sidebar.classList.remove('w-20');
                    sidebar.classList.add('w-64');
                    desktopToggleIcon.classList.remove('rotate-180');
                    sidebarTexts.forEach(el => {
                        el.classList.remove('opacity-0', 'w-0');
                        el.classList.add('opacity-100');
                    });
                }
            });

            // Mobile Toggle
            function toggleMobileSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            }

            mobileMenuBtn.addEventListener('click', toggleMobileSidebar);
            mobileCloseBtn.addEventListener('click', toggleMobileSidebar);
            sidebarOverlay.addEventListener('click', toggleMobileSidebar);
        });

        // Kuota Donut
        new Chart(document.getElementById('quotaChart'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $quota ? $quota->remaining_kg : 0 }}, {{ $quota ? $quota->used_kg : 100 }}],
                    backgroundColor: ['#10b981', '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '72%',
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });

        // Line Chart
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: {!! $commodityPrices->pluck('price_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toJson() !!},
                datasets: [
                    {
                        label: 'Harga (Rp)',
                        data: {!! $commodityPrices->pluck('price')->toJson() !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3
                    }
                ]
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
