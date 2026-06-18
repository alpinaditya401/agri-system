<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin – Agrilink</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style type="text/tailwindcss">
        body { font-family: 'Outfit', sans-serif; }
        .sidebar-link { @apply flex items-center gap-3 px-4 py-2.5 rounded-xl text-emerald-100 hover:bg-emerald-700/50 transition-all text-sm font-medium cursor-pointer; }
        .sidebar-link.active { @apply bg-emerald-700 text-white; }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    <!-- SIDEBAR -->
    <aside class="w-56 bg-emerald-900 flex flex-col flex-shrink-0 h-screen overflow-y-auto">
        <!-- Logo -->
        <div class="flex items-center px-4 py-5 border-b border-emerald-700/50 justify-center">
            <img src="{{ asset('images/agrilink_logo.png') }}" alt="Agrilink Logo" class="h-12 w-auto">
        </div>

        <!-- Nav Links -->
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link active">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.fertilizer.quota.index') }}" class="sidebar-link">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Kuota Pupuk
            </a>
            <a href="{{ route('admin.reports.transactions') }}" class="sidebar-link">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Transaksi
            </a>
            <a href="{{ route('admin.users.index') }}" class="sidebar-link">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                User Management
            </a>
            <a href="{{ route('admin.reports.prices') }}" class="sidebar-link">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Harga BPS <span class="ml-auto text-xs bg-emerald-600 px-2 py-0.5 rounded-full">Aktif</span>
            </a>
            <a href="{{ route('admin.artikel.index') }}" class="sidebar-link">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Kelola Artikel
            </a>

            <div class="border-t border-emerald-700/50 pt-3 mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="sidebar-link">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </a>
                <a href="{{ route('admin.farmers.verify.index') }}" class="sidebar-link">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Verifikasi Petani
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-red-300 hover:bg-red-900/30 text-sm font-medium transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </nav>

        <!-- User Badge -->
        <div class="p-4 border-t border-emerald-700/50">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-emerald-400 rounded-full flex items-center justify-center text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</div>
                <div>
                    <p class="text-white text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="text-emerald-300 text-xs">Administrator</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 overflow-y-auto p-6">

        <!-- Header -->
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Dashboard Admin</h1>
                <p class="text-gray-500 text-sm">Pantau pengguna, transaksi, kuota pupuk, harga komoditas, dan konten Agrilink dari satu tempat.</p>
            </div>
            <div class="flex items-center gap-2 bg-white rounded-xl px-3 py-2 shadow-sm">
                <div class="w-7 h-7 bg-emerald-200 rounded-full flex items-center justify-center text-emerald-700 text-xs font-bold">{{ substr(auth()->user()->name, 0, 1) }}</div>
                <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                <span class="text-xs text-gray-400">Admin</span>
            </div>
        </div>

        <!-- STAT CARDS ROW -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">PENGGUNA</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_users'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Total Terdaftar</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">PESANAN MASUK</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['pending_orders'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Menunggu Diproses</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex justify-between items-start">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">TRANSAKSI PUPUK</p>
                    <span class="text-xs bg-emerald-100 text-emerald-700 font-semibold px-2 py-0.5 rounded-full">{{ $stats['fertilizer_transactions'] ?? 0 }} Baru</span>
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['fertilizer_transactions'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Permintaan Subsidi</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex justify-between items-start">
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">ARTIKEL</p>
                    <span class="text-xs bg-orange-100 text-orange-600 font-semibold px-2 py-0.5 rounded-full">Total</span>
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_articles'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Dipublikasikan</p>
            </div>
        </div>

        <!-- CHARTS ROW -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <!-- Bar Chart: Inventory Pupuk -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">Inventory Pupuk Bersubsidi</h2>
                    <div class="flex gap-3 text-xs text-gray-500">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>Stok Tersedia (Kg)</span>
                    </div>
                </div>
                <canvas id="barChart" height="180"></canvas>
            </div>

            <!-- Donut Chart: Statistik User -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <h2 class="font-bold text-gray-800 mb-4">Statistik User (Farmer/Buyer)</h2>
                <div class="flex items-center justify-between">
                    <div class="w-40 h-40">
                        <canvas id="donutChart"></canvas>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span><span class="text-gray-600">Petani ({{ $stats['total_farmers'] ?? 0 }})</span></div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-teal-400 inline-block"></span><span class="text-gray-600">Pembeli ({{ $stats['total_buyers'] ?? 0 }})</span></div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span><span class="text-gray-600">Distributor ({{ $stats['total_distributors'] ?? 0 }})</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM ROW -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Manajemen Konten & API -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <h2 class="font-bold text-gray-800 mb-4">Manajemen Konten & API</h2>
                <div class="space-y-3">
                    @forelse($latestArticles as $article)
                    <a href="{{ route('admin.artikel.edit', $article) }}" class="flex items-center justify-between bg-gray-50 hover:bg-emerald-50 rounded-xl p-3 transition-colors">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-700 text-sm truncate">{{ $article->title }}</p>
                            <p class="text-xs text-gray-400">{{ ucfirst($article->status) }} • {{ $article->created_at->format('d M Y') }}</p>
                        </div>
                        <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full">{{ $article->view_count ?? 0 }} view</span>
                    </a>
                    @empty
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="font-medium text-gray-700 text-sm">Belum ada artikel</p>
                        <p class="text-xs text-gray-400">Tambahkan artikel untuk edukasi petani dan pembeli.</p>
                    </div>
                    @endforelse
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                        <div>
                            <p class="font-medium text-gray-700 text-sm">Sinkron Harga Komoditas</p>
                            <p class="text-xs text-emerald-600 font-medium">Gunakan laporan untuk cek data BPS terbaru</p>
                        </div>
                        <div class="text-right">
                            <a href="{{ route('admin.reports.prices') }}" class="text-xs text-emerald-600 font-semibold hover:text-emerald-700">Buka laporan</a>
                        </div>
                    </div>
                    <a href="{{ route('admin.artikel.create') }}" class="w-full block text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                        + Tambah Artikel
                    </a>
                </div>
            </div>

            <!-- Verifikasi & Map -->
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800">Verifikasi & Distribusi</h2>
                    <a href="{{ route('admin.farmers.verify.index') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Lihat antrean</a>
                </div>
                <div class="space-y-2 mb-4">
                    @forelse($pendingFarmers as $farmer)
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl px-3 py-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $farmer->name }}</p>
                            <p class="text-xs text-gray-500">{{ $farmer->farmerProfile->farmer_group_name ?? 'Kelompok belum diisi' }}</p>
                        </div>
                        <span class="text-xs bg-yellow-100 text-yellow-700 font-semibold px-2.5 py-1 rounded-full">Pending</span>
                    </div>
                    @empty
                    <div class="bg-gray-50 rounded-xl px-3 py-2">
                        <p class="text-sm font-semibold text-gray-800">Tidak ada verifikasi tertunda</p>
                        <p class="text-xs text-gray-500">Semua pengajuan petani sudah diproses.</p>
                    </div>
                    @endforelse
                </div>
                <div class="bg-emerald-50 border-2 border-dashed border-emerald-200 rounded-xl h-32 flex flex-col items-center justify-center">
                    <span class="text-4xl mb-2">🗺️</span>
                    <p class="text-emerald-700 font-semibold text-sm">Leaflet.js Map</p>
                    <p class="text-emerald-500 text-xs mt-1">Lokasi distribusi petani & distributor</p>
                    <a href="/api/map/combined" target="_blank" class="mt-3 text-xs bg-emerald-600 text-white px-4 py-1.5 rounded-lg font-medium hover:bg-emerald-700 transition-colors">
                        Lihat GeoJSON API
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer note -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-400">Agrilink — Sistem pertanian digital untuk distribusi, niaga, dan informasi harga.</p>
        </div>
    </main>

    <script>
        // Bar Chart
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: {!! $fertilizerInventory->pluck('name')->toJson() !!},
                datasets: [
                    {
                        label: 'Total Stok (Kg)',
                        data: {!! $fertilizerInventory->map(fn($item) => $item->stocks_sum_stock_kg ?? 0)->toJson() !!},
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });

        // Donut Chart
        new Chart(document.getElementById('donutChart'), {
            type: 'doughnut',
            data: {
                labels: ['Petani', 'Pembeli', 'Distributor'],
                datasets: [{
                    data: [
                        {{ $stats['total_farmers'] ?? 0 }}, 
                        {{ $stats['total_buyers'] ?? 0 }}, 
                        {{ $stats['total_distributors'] ?? 0 }}
                    ],
                    backgroundColor: ['#10b981', '#2dd4bf', '#fbbf24'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                cutout: '65%'
            }
        });
    </script>
</body>
</html>
