<x-layouts.app :title="'Laporan Harga Komoditas – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            Laporan Harga Komoditas
            <span class="text-xs bg-emerald-600 text-white px-2 py-0.5 rounded-full">BPS Live</span>
        </h1>
        <p class="text-gray-500 text-sm">Data harga komoditas tersinkron dari Badan Pusat Statistik.</p>
    </x-slot:header>

    <x-bps-region-badge region="Seluruh Wilayah Terpantau" />

    <form method="GET" class="flex flex-wrap gap-2 mb-5">
        <select name="category" onchange="this.form.submit()" class="px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
            <option value="">Semua Kategori</option>
            @foreach ($categories as $cat)
                @if ($cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                @endif
            @endforeach
        </select>
        <a href="{{ route('admin.reports.prices.export', request()->query()) }}" class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-emerald-700">
            Export CSV
        </a>
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">Komoditas</th>
                        <th class="p-4 font-semibold">Kategori</th>
                        <th class="p-4 font-semibold">Harga</th>
                        <th class="p-4 font-semibold">Wilayah</th>
                        <th class="p-4 font-semibold">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($prices as $price)
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 font-medium text-gray-800">{{ $price->commodity_name }}</td>
                            <td class="p-4"><span class="bg-emerald-50 text-emerald-700 text-xs font-semibold px-2.5 py-1 rounded-md">{{ $price->category }}</span></td>
                            <td class="p-4 text-gray-700 font-semibold">Rp {{ number_format($price->price, 0, ',', '.') }}/{{ $price->unit }}</td>
                            <td class="p-4 text-gray-500">{{ $price->region ?? 'Nasional' }}</td>
                            <td class="p-4 text-gray-400 text-xs">{{ \Carbon\Carbon::parse($price->price_date)->translatedFormat('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 text-sm italic">Belum ada data harga.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $prices->links() }}</div>
    </div>
</x-layouts.app>
