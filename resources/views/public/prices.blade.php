<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harga Komoditas - Agrilink</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
    <x-public-nav active="prices" />

    <main class="pt-20">
        <section class="border-b border-emerald-900/10 bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-900 text-white">
            <div class="ag-container py-14 md:py-18">
                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-end">
                    <div>
                        <p class="text-xs font-bold uppercase text-emerald-200 tracking-[0.24em]">Sumber Data Terpercaya</p>
                        <h1 class="mt-4 text-4xl font-black leading-none md:text-6xl">Harga Komoditas</h1>
                        <p class="mt-5 max-w-2xl text-base leading-8 text-emerald-50/78">
                            Pantau perkembangan harga komoditas pertanian dari sumber data terpercaya.
                        </p>
                    </div>
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-5 backdrop-blur-xl">
                        <p class="text-xs font-bold uppercase text-emerald-100/75">Cakupan Data</p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-white p-4 text-slate-900">
                                <p class="text-2xl font-black text-emerald-700">{{ number_format(collect($prices)->count(), 0, ',', '.') }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">komoditas</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                                <p class="text-2xl font-black text-white">BPS</p>
                                <p class="mt-1 text-xs font-semibold text-emerald-50/70">sumber utama</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ag-container py-8">
            <x-bps-region-badge region="Rata-rata Nasional" />

            @if ($bpsArticle)
                <a href="{{ route('public.articles.show', $bpsArticle->slug) }}" class="mt-5 flex items-start gap-4 rounded-3xl border border-emerald-200 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50">
                    <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" /></svg>
                    </span>
                    <span>
                        <span class="block text-sm font-black text-slate-950">Artikel sumber data BPS</span>
                        <span class="mt-1 block text-sm leading-6 text-slate-500">Lihat konteks tabel BPS utuh yang dipakai Agrilink, termasuk link resmi ke halaman statistik BPS.</span>
                    </span>
                </a>
            @endif

            @if (count($prices) === 0)
                <x-ui.empty-state title="Data harga belum tersedia" message="Sinkronisasi data harga komoditas sedang berjalan. Coba kembali beberapa saat lagi." class="mt-8" />
            @else
                <div class="mt-8 flex flex-wrap gap-2" id="categoryFilter">
                    <button type="button" onclick="filterCat('all', this)" class="ag-chip ag-chip-active cat-btn">Semua</button>
                    @foreach ($categories as $cat)
                        @if ($cat)
                            <button type="button" onclick="filterCat('{{ $cat }}', this)" class="ag-chip cat-btn">{{ ucfirst($cat) }}</button>
                        @endif
                    @endforeach
                </div>

                <div class="ag-table-wrap mt-6">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-5 py-4 font-black">Komoditas</th>
                                <th class="px-5 py-4 font-black">Kategori</th>
                                <th class="px-5 py-4 font-black">Harga</th>
                                <th class="px-5 py-4 font-black">Wilayah</th>
                                <th class="px-5 py-4 font-black">Tanggal</th>
                                <th class="px-5 py-4 font-black">Sumber</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white" id="priceGrid">
                            @foreach ($prices as $price)
                                <tr class="price-card transition hover:bg-emerald-50/40" data-cat="{{ $price->category }}">
                                    <td class="px-5 py-4">
                                        <p class="font-black text-slate-950">{{ $price->commodity_name }}</p>
                                        @php
                                            $rawData = is_array($price->raw_data ?? null) ? $price->raw_data : [];
                                            $note = data_get($rawData, 'source_note');
                                            $tables = config('bps_sources.tables', []);
                                            $sourceTables = collect(data_get($rawData, 'source_tables', []));

                                            if ($sourceTables->isEmpty()) {
                                                $sourceTables = collect(config("bps_sources.commodities.{$price->commodity_code}", []))
                                                    ->map(fn($key) => $tables[$key] ?? null)
                                                    ->filter()
                                                    ->values();
                                            }
                                        @endphp
                                        @if($note)
                                            <p class="mt-1 max-w-xs text-xs leading-5 text-slate-500">{{ $note }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <x-ui.badge tone="success">{{ $price->category ?: 'Umum' }}</x-ui.badge>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-lg font-black text-emerald-700">Rp {{ number_format($price->price, 0, ',', '.') }}</p>
                                        <p class="text-xs font-semibold text-slate-400">per {{ $price->unit }}</p>
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-slate-600">{{ $price->region ?? 'Nasional' }}</td>
                                    <td class="px-5 py-4 text-slate-500">{{ \Carbon\Carbon::parse($price->price_date)->translatedFormat('d M Y') }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex min-w-48 flex-wrap gap-2">
                                            @forelse($sourceTables as $table)
                                                <a href="{{ $table['url'] }}" target="_blank" rel="noopener" class="ag-chip px-3 py-1 text-[11px]">
                                                    {{ $table['short_label'] }}
                                                </a>
                                            @empty
                                                <span class="text-xs font-semibold text-slate-400">{{ $price->source ?? 'BPS' }}</span>
                                            @endforelse
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs font-semibold text-slate-400">
        Agrilink - Sistem pertanian digital untuk distribusi, niaga, dan informasi harga.
    </footer>

    <script>
        function filterCat(cat, btn) {
            document.querySelectorAll('.cat-btn').forEach((button) => {
                button.classList.remove('ag-chip-active');
            });
            btn.classList.add('ag-chip-active');
            document.querySelectorAll('.price-card').forEach((card) => {
                card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
