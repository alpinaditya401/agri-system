<x-layouts.app :title="'Pesanan Saya - Agrilink'">
    <x-slot:sidebar>
        @include('buyer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading flex items-center gap-2">
            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2" /></svg>
            Pesanan Saya
        </h1>
        <p class="mt-1 text-sm text-slate-500">Riwayat dan status seluruh pesanan Anda.</p>
    </x-slot:header>

    @if ($orders->isEmpty())
        <x-ui.empty-state title="Belum ada pesanan" message="Pesanan akan muncul setelah Anda checkout produk dari marketplace.">
            <a href="{{ route('products.index') }}" class="ag-btn-primary">Belanja Produk</a>
        </x-ui.empty-state>
    @else
        <div class="ag-table-wrap">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4 font-black">Nomor</th>
                        <th class="px-5 py-4 font-black">Petani</th>
                        <th class="px-5 py-4 font-black">Total</th>
                        <th class="px-5 py-4 font-black">Pembayaran</th>
                        <th class="px-5 py-4 font-black">Status</th>
                        <th class="px-5 py-4 font-black">Tanggal</th>
                        <th class="px-5 py-4 font-black">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($orders as $order)
                        <tr class="transition hover:bg-emerald-50/40">
                            <td class="px-5 py-4 font-black text-emerald-700">#{{ $order->order_number }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-700">{{ $order->farmer->name ?? 'Petani' }}</td>
                            <td class="px-5 py-4 font-black text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                <x-ui.badge :tone="$order->payment_status">{{ ucfirst($order->payment_status) }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-4">
                                <x-ui.badge :tone="$order->order_status">{{ ucfirst($order->order_status) }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-4 text-xs font-semibold text-slate-400">{{ $order->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ route('buyer.orders.show', $order) }}" class="ag-btn-secondary min-h-10 px-4 py-2 text-xs">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $orders->links() }}
        </div>
    @endif
</x-layouts.app>
