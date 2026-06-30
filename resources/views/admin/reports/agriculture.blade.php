<x-layouts.app :title="'Statistik Petani & Pupuk - Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="ag-heading">Statistik Petani & Pupuk</h1>
                <p class="mt-1 text-sm text-slate-500">Laporan ringkas petani Indonesia, kuota pupuk, stok distributor subsidi, dan transaksi pupuk tahun {{ $year }}.</p>
            </div>
            <a href="{{ route('admin.reports.agriculture.export', request()->query()) }}" class="ag-btn-primary">
                Export CSV
            </a>
        </div>
    </x-slot:header>

    <x-dashboard-region-filter :filters="$regionFilters ?? []" :options="$regionOptions ?? []" description="Filter statistik petani, distributor, stok pupuk, kuota, dan transaksi berdasarkan wilayah." />

    <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
        @foreach (($regionFilters ?? []) as $key => $value)
            @if(filled($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <label class="block">
            <span class="text-xs font-black uppercase tracking-wide text-slate-400">Tahun laporan</span>
            <select name="year" onchange="this.form.submit()" class="mt-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-400">
                @for ($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </label>
    </form>

    @php
        $cards = [
            ['label' => 'Total Petani', 'value' => $statistics['farmers']['total'], 'desc' => $statistics['farmers']['verified'] . ' terverifikasi', 'tone' => 'emerald'],
            ['label' => 'Luas Lahan', 'value' => number_format($statistics['farmers']['land_area'], 2, ',', '.') . ' ha', 'desc' => 'akumulasi lahan terdata', 'tone' => 'lime'],
            ['label' => 'Distributor Aktif', 'value' => $statistics['distributors']['active_subsidy'], 'desc' => 'subsidi terverifikasi', 'tone' => 'sky'],
            ['label' => 'Stok Pupuk Tersedia', 'value' => number_format($statistics['fertilizers']['available_stock'], 0, ',', '.') . ' kg', 'desc' => $statistics['fertilizers']['active_types'] . ' jenis pupuk aktif', 'tone' => 'amber'],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <div class="ag-card p-5">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">{{ $card['label'] }}</p>
                <p class="mt-3 text-2xl font-black text-slate-950">{{ $card['value'] }}</p>
                <p class="mt-1 text-sm font-semibold text-slate-500">{{ $card['desc'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
        <section class="ag-card p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="ag-label">Pupuk subsidi</p>
                    <h2 class="mt-2 text-xl font-black text-slate-950">Kuota, stok, dan penyaluran</h2>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">{{ number_format($statistics['fertilizers']['transaction_count'], 0, ',', '.') }} transaksi</span>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase tracking-wide text-slate-400">Kuota dialokasikan</p>
                    <p class="mt-2 text-xl font-black text-slate-900">{{ number_format($statistics['fertilizers']['allocated_quota'], 0, ',', '.') }} kg</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase tracking-wide text-slate-400">Kuota terpakai</p>
                    <p class="mt-2 text-xl font-black text-slate-900">{{ number_format($statistics['fertilizers']['used_quota'], 0, ',', '.') }} kg</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase tracking-wide text-slate-400">Tersalurkan</p>
                    <p class="mt-2 text-xl font-black text-slate-900">{{ number_format($statistics['fertilizers']['dispensed_kg'], 0, ',', '.') }} kg</p>
                </div>
            </div>

            <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Jenis Pupuk</th>
                            <th class="px-4 py-3">Stok</th>
                            <th class="px-4 py-3">Reserved</th>
                            <th class="px-4 py-3">Tersedia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($statistics['fertilizers']['stock_by_type'] as $stock)
                            <tr>
                                <td class="px-4 py-3 font-black text-slate-900">{{ $stock['name'] }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ number_format($stock['stock_kg'], 0, ',', '.') }} kg</td>
                                <td class="px-4 py-3 text-slate-600">{{ number_format($stock['reserved_kg'], 0, ',', '.') }} kg</td>
                                <td class="px-4 py-3 font-bold text-emerald-700">{{ number_format($stock['available_kg'], 0, ',', '.') }} kg</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400">Belum ada stok pupuk pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="space-y-5">
            <section class="ag-card p-5">
                <p class="ag-label">Komoditas petani</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Hasil tanam terdata</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse ($statistics['farmers']['commodities'] as $commodity)
                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">{{ $commodity }}</span>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada komoditas petani.</p>
                    @endforelse
                </div>
            </section>

            <section class="ag-card p-5">
                <p class="ag-label">Status transaksi pupuk</p>
                <div class="mt-4 space-y-3">
                    @forelse ($statistics['fertilizers']['transaction_status'] as $status)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-black capitalize text-slate-900">{{ str_replace('_', ' ', $status['status']) }}</p>
                                <p class="text-xs font-semibold text-slate-500">{{ number_format($status['approved_kg'], 0, ',', '.') }} kg disetujui</p>
                            </div>
                            <span class="text-lg font-black text-slate-950">{{ $status['count'] }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada transaksi pupuk.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>

    <section class="ag-card mt-5 p-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="ag-label">Wilayah Indonesia</p>
                <h2 class="mt-2 text-xl font-black text-slate-950">Sebaran petani dan distributor subsidi aktif</h2>
            </div>
        </div>
        <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Wilayah</th>
                        <th class="px-4 py-3">Petani</th>
                        <th class="px-4 py-3">Luas Lahan</th>
                        <th class="px-4 py-3">Distributor Aktif</th>
                        <th class="px-4 py-3">Komoditas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($statistics['regions'] as $region)
                        <tr>
                            <td class="px-4 py-3 font-black text-slate-900">{{ $region['district'] ?? '-' }}, {{ $region['province'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ number_format($region['farmer_count'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ number_format($region['land_area'], 2, ',', '.') }} ha</td>
                            <td class="px-4 py-3 font-bold text-sky-700">{{ number_format($region['active_distributors'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $region['commodities']->take(4)->implode(', ') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">Belum ada data wilayah petani.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
