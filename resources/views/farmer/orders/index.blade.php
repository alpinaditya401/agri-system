<x-layouts.app :title="'Pesanan Masuk – Agrilink'">
    <x-slot:sidebar>
        @include('farmer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Pesanan Masuk
        </h1>
        <p class="text-gray-500 text-sm">Kelola pesanan dari pembeli.</p>
    </x-slot:header>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">Nomor</th>
                        <th class="p-4 font-semibold">Pembeli</th>
                        <th class="p-4 font-semibold">Total</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Tanggal</th>
                        <th class="p-4 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 font-medium text-emerald-700">#{{ $order->order_number }}</td>
                            <td class="p-4 text-gray-800">{{ $order->buyer->name ?? 'Pembeli' }}</td>
                            <td class="p-4 text-gray-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="p-4"><span class="bg-gray-100 text-gray-700 text-xs font-semibold px-2.5 py-1 rounded-md">{{ ucfirst($order->order_status) }}</span></td>
                            <td class="p-4 text-gray-400 text-xs">{{ $order->created_at->translatedFormat('d M Y') }}</td>
                            <td class="p-4"><a href="{{ route('farmer.orders.show', $order) }}" class="text-emerald-600 hover:text-emerald-800 font-medium">Detail</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400 text-sm italic">Belum ada pesanan masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $orders->links() }}
    </div>
</x-layouts.app>
