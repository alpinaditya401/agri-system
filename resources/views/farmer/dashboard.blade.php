<x-layouts.app :title="'Dashboard Petani – Agrilink'">
    <x-slot:sidebar>
        @include('farmer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading">Selamat Datang, {{ auth()->user()->name ?? 'Petani' }} (Petani)</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola produk, proses pesanan pembeli, pantau harga komoditas, dan ajukan pupuk bersubsidi.</p>
    </x-slot:header>

    <x-dashboard-region-filter :filters="$regionFilters ?? []" :options="$regionOptions ?? []" description="Filter pesanan berdasarkan wilayah pembeli dan sesuaikan insight harga wilayah jika tersedia." />

    <!-- QUICK ACCESS BUTTONS -->
    <div class="mb-5 flex flex-wrap gap-3">
        <a href="{{ route('public.prices') }}" class="ag-btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 19.5h16.5M6.75 16.5V9M12 16.5V4.5M17.25 16.5v-6" /></svg>
            Info Harga
        </a>
        <a href="{{ route('farmer.produk.create') }}" class="ag-btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5.25v13.5M5.25 12h13.5" /></svg>
            Tambah Produk
        </a>
        <a href="{{ route('farmer.fertilizer.index') }}" class="ag-btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21c0-4.75 2.5-8.25 7-10-4.75-.75-8.25.75-10.5 4.5M12 21c0-5.25-2.5-9-7-11 5-.75 8.75 1 11 5.25M12 21V9" /></svg>
            Ajukan Pupuk
        </a>
        <a href="{{ route('chat.index') }}" class="ag-btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0Zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0Zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 4.1-4.03 7.5-9 7.5-1.1 0-2.15-.17-3.12-.48L3 21l1.75-4.25C3.65 15.42 3 13.79 3 12c0-4.1 4.03-7.5 9-7.5s9 3.4 9 7.5Z" /></svg>
            Chat Pembeli
        </a>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="ag-card p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Produk Aktif</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['active_products'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">dari {{ $stats['total_products'] ?? 0 }} produk</p>
        </div>
        <div class="ag-card p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Pesanan Masuk</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['incoming_orders'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">perlu diproses</p>
        </div>
        <div class="ag-card p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Omzet Dibayar</p>
            <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">transaksi lunas</p>
        </div>
        <div class="ag-card p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Kuota Pupuk</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($quota ? $quota->remaining_kg : 0, 0, ',', '.') }} Kg</p>
            <p class="text-xs text-gray-500 mt-1">tersisa</p>
        </div>
    </div>

    <!-- MIDDLE GRID: Subsidi + Map -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

        <div class="ag-card p-5">
            <h2 class="font-bold text-gray-800 mb-4">Verifikasi Subsidi</h2>
            <p class="text-xs text-gray-400 uppercase font-semibold mb-2">NIK & Kelompok Tani</p>
            <div class="relative mb-4">
                <input type="text" value="{{ auth()->user()->farmerProfile->nik ?? 'NIK belum diisi' }} - {{ auth()->user()->farmerProfile->farmer_group_name ?? 'Kelompok belum diisi' }}" readonly
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-400 bg-gray-50 text-gray-500">
                <svg class="w-4 h-4 text-emerald-600 absolute right-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>

            <h3 class="font-semibold text-gray-700 text-sm mb-3">Kuota Tersisa: {{ $quota ? $quota->remaining_kg : 0 }} Kg</h3>
            <div class="flex items-center justify-center">
                <div class="relative w-32 h-32">
                    <canvas id="quotaChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21c0-4.75 2.5-8.25 7-10-4.75-.75-8.25.75-10.5 4.5M12 21c0-5.25-2.5-9-7-11 5-.75 8.75 1 11 5.25M12 21V9" /></svg>
                        </span>
                        <span class="text-xs font-semibold text-gray-600">{{ $quota && $quota->allocated_kg > 0 ? round(($quota->remaining_kg / $quota->allocated_kg) * 100) : 0 }}%</span>
                    </div>
                </div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-2">
                <span>Terpakai: {{ $quota ? $quota->used_kg : 0 }} Kg</span>
                <span>Total: {{ $quota ? $quota->allocated_kg : 0 }} Kg</span>
            </div>
        </div>

        <!-- Peta distribusi (real, terintegrasi dari GeoJSON) -->
        <div class="ag-card p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-800">Peta Distribusi & Lokasi Distributor</h2>
                <a href="{{ route('public.map') }}" class="text-xs text-emerald-600 font-semibold hover:text-emerald-700">Buka layar penuh</a>
            </div>
            <x-leaflet-map height="220px" :endpoint="route('api.map.combined', array_filter($regionFilters ?? []))" />
        </div>
    </div>

    <!-- BOTTOM GRID: Chart BPS + Katalog Mini -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <div class="ag-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-800">Info Harga BPS</h2>
                <div class="flex gap-3 text-xs text-gray-500">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>Harga (Rp)</span>
                </div>
            </div>
            <canvas id="lineChart" height="160"></canvas>
        </div>

        <div class="ag-card p-5">
            <h2 class="font-bold text-gray-800 mb-4">Produk Anda</h2>
            <div class="border-t pt-4">
                <div class="space-y-3">
                    @forelse($products as $product)
                    @php
                        $reference = $productReferencePrices[$product->id] ?? null;
                        $diff = $reference ? (float) $product->price_per_unit - (float) $reference->price : null;
                    @endphp
                    <div class="flex gap-3 items-center">
                        <x-product-visual :product="$product" class="h-14 w-14 flex-shrink-0 rounded-xl" icon-class="h-6 w-6" frame-class="h-10 w-10 rounded-xl" image-class="h-full w-full object-cover" />
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500">Rp {{ number_format($product->price_per_unit, 0, ',', '.') }}/{{ $product->unit }} • Stok: {{ $product->stock_quantity }}</p>
                            @if($reference)
                                <p class="text-[11px] text-gray-500 mt-1">
                                    Referensi BPS: Rp {{ number_format($reference->price, 0, ',', '.') }}/{{ $reference->unit }}
                                    <span class="{{ $diff <= 0 ? 'text-emerald-600' : 'text-orange-600' }} font-semibold">
                                        ({{ $diff <= 0 ? 'di bawah' : 'di atas' }} Rp {{ number_format(abs($diff), 0, ',', '.') }})
                                    </span>
                                </p>
                            @else
                                <p class="text-[11px] text-gray-400 mt-1">Referensi BPS rupiah belum tersedia untuk produk ini</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-500 italic">Belum ada produk yang dijual.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="ag-card mt-5 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-gray-800">Pesanan Terbaru</h2>
            <a href="{{ route('farmer.orders.index') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Kelola semua</a>
        </div>
        <div class="ag-table-wrap">
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

    @push('scripts')
    <script>
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

        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: {!! $commodityPrices->pluck('commodity_name')->toJson() !!},
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
    @endpush
</x-layouts.app>

