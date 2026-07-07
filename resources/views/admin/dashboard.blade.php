<x-layouts.app :title="auth()->user()->isAdminMaster() ? 'Dashboard Admin Master – Agrilink' : 'Dashboard Admin – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading">{{ auth()->user()->isAdminMaster() ? 'Dashboard Admin Master' : 'Dashboard Admin' }}</h1>
        <p class="mt-1 text-sm text-slate-500">
            {{ auth()->user()->isAdminMaster()
                ? 'Kontrol penuh admin, role pengguna, transaksi, kuota pupuk, harga komoditas, dan konten Agrilink.'
                : 'Pantau pengguna, transaksi, kuota pupuk, harga komoditas, dan konten Agrilink dari satu tempat.' }}
        </p>
    </x-slot:header>

    @push('head')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush

    <x-dashboard-region-filter :filters="$regionFilters ?? []" :options="$regionOptions ?? []" description="Filter statistik pengguna, order, permintaan pupuk, stok distributor, dan antrean verifikasi berdasarkan wilayah." />

    <!-- STAT CARDS ROW -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="ag-card p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">PENGGUNA</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total_users'] ?? 0 }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Terdaftar</p>
        </div>
        <div class="ag-card p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">PESANAN MASUK</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['pending_orders'] ?? 0 }}</p>
            <p class="text-sm text-gray-500 mt-1">Menunggu Diproses</p>
        </div>
        <div class="ag-card p-5">
            <div class="flex justify-between items-start">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">TRANSAKSI PUPUK</p>
                <span class="text-xs bg-emerald-100 text-emerald-700 font-semibold px-2 py-0.5 rounded-full">{{ $stats['pending_fertilizer_transactions'] ?? 0 }} Baru</span>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total_fertilizer_transactions'] ?? 0 }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Permintaan Subsidi</p>
        </div>
        <div class="ag-card p-5">
            <div class="flex justify-between items-start">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">DISTRIBUTOR AKTIF</p>
                <span class="text-xs bg-sky-100 text-sky-700 font-semibold px-2 py-0.5 rounded-full">{{ $stats['pending_distributor_verifications'] ?? 0 }} pending</span>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['active_subsidy_distributors'] ?? 0 }}</p>
            <p class="text-sm text-gray-500 mt-1">Subsidi terverifikasi</p>
        </div>
    </div>

    <!-- CHARTS ROW -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
        <div class="ag-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-800">Inventory Pupuk Bersubsidi</h2>
                <div class="flex gap-3 text-xs text-gray-500">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>Stok Tersedia (Kg)</span>
                </div>
            </div>
            <canvas id="barChart" height="180"></canvas>
        </div>

        <div class="ag-card p-5">
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
        <div class="ag-card p-5">
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
                <a href="{{ route('admin.artikel.create') }}" class="ag-btn-primary w-full">
                    Tambah Artikel
                </a>
            </div>
        </div>

        <!-- Verifikasi & Map (Leaflet asli) -->
        <div class="ag-card p-5">
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
            <div class="mb-4 space-y-2">
                @forelse($pendingDistributors as $distributor)
                <div class="flex items-center justify-between rounded-xl bg-sky-50 px-3 py-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $distributor->name }}</p>
                        <p class="text-xs text-gray-500">{{ $distributor->distributorProfile->company_name ?? 'Usaha belum diisi' }}</p>
                    </div>
                    <a href="{{ route('admin.distributors.verify.index') }}" class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-bold text-sky-700">Distributor</a>
                </div>
                @empty
                <div class="rounded-xl bg-gray-50 px-3 py-2">
                    <p class="text-sm font-semibold text-gray-800">Tidak ada distributor tertunda</p>
                    <p class="text-xs text-gray-500">Semua pengajuan distributor sudah diproses.</p>
                </div>
                @endforelse
            </div>
            <x-leaflet-map height="200px" :endpoint="route('api.map.combined', array_filter($regionFilters ?? []))" />
        </div>
    </div>

    @push('scripts')
    <script>
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
    @endpush
</x-layouts.app>

