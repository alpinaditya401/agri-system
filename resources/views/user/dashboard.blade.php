<x-layouts.app :title="'Dashboard Pembeli - Agrilink'">
    <x-slot:sidebar>
        @include('buyer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h1 class="ag-heading">Selamat Datang, {{ auth()->user()->name ?? 'Pembeli' }} (Pembeli)</h1>
                <p class="mt-1 text-sm text-slate-500">Belanja hasil tani, pantau pesanan, cek keranjang, dan lihat harga komoditas terbaru.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('products.index') }}" class="ag-btn-primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 7.5h16.5M5.25 7.5l1.2 11.25A2.25 2.25 0 008.7 20.75h6.6a2.25 2.25 0 002.25-2L18.75 7.5M9 7.5V6a3 3 0 016 0v1.5" /></svg>
                    Belanja Sekarang
                </a>
                <a href="{{ route('buyer.cart.index') }}" class="ag-btn-secondary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h2.2l2.1 11.2a2.25 2.25 0 002.2 1.8h7.9a2.25 2.25 0 002.1-1.45L21 7.5H6.1M9 20.25h.01M17 20.25h.01" /></svg>
                    Keranjang
                </a>
                <a href="{{ route('chat.index') }}" class="ag-btn-secondary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0Zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0Zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 4.1-4.03 7.5-9 7.5-1.1 0-2.15-.17-3.12-.48L3 21l1.75-4.25C3.65 15.42 3 13.79 3 12c0-4.1 4.03-7.5 9-7.5s9 3.4 9 7.5Z" /></svg>
                    Chat Petani
                </a>
                <a href="{{ route('buyer.become-farmer.create') }}" class="ag-btn-secondary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.75c-3.75 0-6.75 2.1-6.75 4.7 0 4.05 4.65 5.8 6.75 8.05 2.1-2.25 6.75-4 6.75-8.05 0-2.6-3-4.7-6.75-4.7Zm0 0V3m-4.5 7.5c1.5 0 3 .75 4.5 2.25 1.5-1.5 3-2.25 4.5-2.25" /></svg>
                    Daftar Jadi Penjual
                </a>
                <a href="{{ route('buyer.become-distributor.create') }}" class="ag-btn-secondary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-8 4h5M5 5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5z" /></svg>
                    Ajukan Distributor
                </a>
            </div>
        </div>
    </x-slot:header>

    <x-dashboard-region-filter :filters="$regionFilters ?? []" :options="$regionOptions ?? []" description="Filter rekomendasi produk, pesanan, dan harga komoditas berdasarkan wilayah petani/penjual." />

    @php
        $commodityPriceList = collect($commodityPrices ?? []);
        $statCards = [
            ['label' => 'Total Pesanan', 'value' => $stats['total_orders'] ?? 0, 'desc' => 'semua transaksi', 'tone' => 'emerald', 'icon' => 'M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.01M3.75 12h.01M3.75 17.25h.01'],
            ['label' => 'Menunggu', 'value' => $stats['pending_orders'] ?? 0, 'desc' => 'perlu dipantau', 'tone' => 'amber', 'icon' => 'M12 6v6l3.5 2M21 12a9 9 0 11-18 0 9 9 0 0118 0Z'],
            ['label' => 'Selesai', 'value' => $stats['completed_orders'] ?? 0, 'desc' => 'pesanan diterima', 'tone' => 'sky', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z'],
            ['label' => 'Keranjang', 'value' => $stats['cart_items'] ?? 0, 'desc' => 'item siap checkout', 'tone' => 'teal', 'icon' => 'M2.25 3h2.2l2.1 11.2a2.25 2.25 0 002.2 1.8h7.9a2.25 2.25 0 002.1-1.45L21 7.5H6.1M9 20.25h.01M17 20.25h.01'],
        ];
    @endphp

    <div class="space-y-5">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($statCards as $index => $card)
                <div class="ag-card p-5" data-reveal style="--reveal-delay: {{ $index * 60 }}ms">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-slate-400">{{ $card['label'] }}</p>
                            <p class="mt-3 text-3xl font-black text-slate-950">{{ $card['value'] }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $card['desc'] }}</p>
                        </div>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $card['tone'] === 'emerald' ? 'bg-emerald-50 text-emerald-700' : ($card['tone'] === 'amber' ? 'bg-amber-50 text-amber-700' : ($card['tone'] === 'sky' ? 'bg-sky-50 text-sky-700' : 'bg-teal-50 text-teal-700')) }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}" />
                            </svg>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
            <section class="ag-card p-5 md:p-6" data-reveal>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="ag-label">Rekomendasi</p>
                        <h2 class="mt-2 text-xl font-black text-slate-950">Produk tani pilihan</h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="ag-btn-secondary px-4 py-2">Lihat katalog</a>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                    @forelse($featuredProducts as $product)
                        @php
                            $reference = $productReferencePrices[$product->id] ?? null;
                            $diff = $reference ? (float) $product->price_per_unit - (float) $reference->price : null;
                        @endphp
                        <a href="{{ route('products.show', $product->slug) }}" class="group flex min-h-full flex-col rounded-3xl border border-slate-200 bg-slate-50 p-3 transition duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:bg-emerald-50 hover:shadow-md">
                            <x-product-visual :product="$product" class="aspect-[16/10] rounded-2xl" icon-class="h-9 w-9" frame-class="h-16 w-16 rounded-2xl" />
                            <div class="flex flex-1 flex-col pt-4">
                                <p class="line-clamp-1 font-black text-slate-950">{{ $product->name }}</p>
                                <p class="mt-1 line-clamp-1 text-xs font-semibold text-slate-500">{{ $product->farmer->name ?? 'Petani Agrilink' }}</p>
                                <p class="mt-3 text-base font-black text-emerald-700">Rp {{ number_format($product->price_per_unit, 0, ',', '.') }}/{{ $product->unit }}</p>
                                @if($reference)
                                    <p class="mt-2 text-xs leading-5 text-slate-500">
                                        BPS: Rp {{ number_format($reference->price, 0, ',', '.') }}/{{ $reference->unit }}
                                        <span class="{{ $diff <= 0 ? 'text-emerald-600' : 'text-orange-600' }} font-bold">
                                            {{ $diff <= 0 ? 'lebih rendah' : 'lebih tinggi' }} Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                        </span>
                                    </p>
                                @else
                                    <p class="mt-2 text-xs text-slate-400">Referensi BPS rupiah belum tersedia</p>
                                @endif
                            </div>
                        </a>
                    @empty
                        <x-ui.empty-state title="Belum ada produk aktif" message="Produk petani akan tampil di sini setelah tersedia." class="md:col-span-2 2xl:col-span-3" />
                    @endforelse
                </div>
            </section>

            <aside class="ag-card p-5 md:p-6" data-reveal>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="ag-label">Keranjang</p>
                        <h2 class="mt-2 text-xl font-black text-slate-950">Ringkasan belanja</h2>
                    </div>
                    <a href="{{ route('buyer.cart.index') }}" class="text-sm font-black text-emerald-700 hover:text-emerald-800">Detail</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($cartItems as $item)
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3">
                            <x-product-visual :product="$item->product" class="h-14 w-14 flex-shrink-0 rounded-2xl" icon-class="h-5 w-5" frame-class="h-9 w-9 rounded-xl" image-class="h-full w-full object-cover" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-black text-slate-900">{{ $item->product->name ?? 'Produk tidak tersedia' }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $item->quantity }} {{ $item->product->unit ?? 'item' }}</p>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state title="Keranjang kosong" message="Tambahkan produk dari katalog untuk mulai checkout." class="p-5" />
                    @endforelse
                </div>
            </aside>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <section class="ag-card p-5 md:p-6" data-reveal>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="ag-label">Pesanan</p>
                        <h2 class="mt-2 text-xl font-black text-slate-950">Pesanan terbaru</h2>
                    </div>
                    <a href="{{ route('buyer.orders.index') }}" class="ag-btn-secondary px-4 py-2">Kelola semua</a>
                </div>
                <div class="ag-table-wrap mt-5">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                                <th class="p-3 font-black">Nomor</th>
                                <th class="p-3 font-black">Petani</th>
                                <th class="p-3 font-black">Total</th>
                                <th class="p-3 font-black">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($recentOrders as $order)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="p-3"><a href="{{ route('buyer.orders.show', $order) }}" class="font-black text-emerald-700">#{{ $order->order_number }}</a></td>
                                    <td class="p-3 font-semibold text-slate-700">{{ $order->farmer->name ?? 'Petani' }}</td>
                                    <td class="p-3 font-semibold text-slate-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td class="p-3"><x-ui.badge :tone="$order->order_status">{{ $order->order_status_label }}</x-ui.badge></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-5">
                                        <x-ui.empty-state title="Belum ada pesanan" message="Pesanan yang Anda buat akan muncul di tabel ini." class="border-0 bg-slate-50 p-5 shadow-none" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="ag-card p-5 md:p-6" data-reveal>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="ag-label">Harga Komoditas</p>
                        <h2 class="mt-2 text-xl font-black text-slate-950">Info harga terbaru</h2>
                    </div>
                    <a href="{{ route('public.prices') }}" class="ag-btn-secondary px-4 py-2">Lihat detail</a>
                </div>
                <div class="mt-5 min-h-[190px]">
                    <canvas id="priceChart" height="155"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @forelse($commodityPriceList->take(4) as $price)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
                            <p class="truncate text-sm font-black text-slate-900">{{ $price->commodity_name }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $price->region ?? 'Nasional' }}</p>
                            <p class="mt-2 text-sm font-black text-emerald-700">Rp {{ number_format($price->price, 0, ',', '.') }}/{{ $price->unit }}</p>
                        </div>
                    @empty
                        <x-ui.empty-state title="Data harga belum tersedia" message="Harga komoditas akan tampil setelah data tersinkron." class="sm:col-span-2 p-5" />
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
    <script>
        new Chart(document.getElementById('priceChart'), {
            type: 'bar',
            data: {
                labels: {!! $commodityPriceList->pluck('commodity_name')->values()->toJson() !!},
                datasets: [{
                    label: 'Harga',
                    data: {!! $commodityPriceList->pluck('price')->values()->toJson() !!},
                    backgroundColor: 'rgba(16,185,129,0.78)',
                    borderColor: '#059669',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => `Harga: Rp ${Number(context.parsed.y || 0).toLocaleString('id-ID')}`,
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: {
                            font: { size: 10 },
                            callback: (value) => `Rp ${Number(value).toLocaleString('id-ID')}`,
                        },
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 0 } }
                }
            }
        });
    </script>
    @endpush
</x-layouts.app>

