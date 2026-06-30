<x-layouts.app :title="'Laporan Transaksi – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">Laporan Transaksi E-Commerce</h1>
        <p class="text-gray-500 text-sm">Pantau seluruh transaksi jual-beli produk pertanian.</p>
    </x-slot:header>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-emerald-500">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Total Pesanan</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary['total_orders'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-500">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Total Pendapatan</p>
            <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-yellow-500">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Menunggu</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary['pending_orders'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-teal-500">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Selesai</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary['completed_orders'] ?? 0 }}</p>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap gap-2 mb-5">
        <select name="status" onchange="this.form.submit()" class="px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
            <option value="">Semua Status</option>
            @foreach (['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'] as $st)
                <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
        <input type="date" name="to" value="{{ request('to') }}" class="px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors">Filter</button>
        <a href="{{ route('admin.reports.transactions.export', request()->query()) }}" class="inline-flex items-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200 transition-colors hover:bg-emerald-50">
            Export CSV
        </a>
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">Nomor</th>
                        <th class="p-4 font-semibold">Pembeli</th>
                        <th class="p-4 font-semibold">Petani</th>
                        <th class="p-4 font-semibold">Total</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 font-medium text-emerald-700">#{{ $order->order_number }}</td>
                            <td class="p-4 text-gray-800">{{ $order->buyer->name ?? '-' }}</td>
                            <td class="p-4 text-gray-600">{{ $order->farmer->name ?? '-' }}</td>
                            <td class="p-4 text-gray-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="p-4"><span class="bg-gray-100 text-gray-700 text-xs font-semibold px-2.5 py-1 rounded-md">{{ ucfirst($order->order_status) }}</span></td>
                            <td class="p-4 text-gray-400 text-xs">{{ $order->created_at->translatedFormat('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400 text-sm italic">Tidak ada transaksi yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $orders->links() }}</div>
    </div>
</x-layouts.app>
