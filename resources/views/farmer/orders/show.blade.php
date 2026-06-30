<x-layouts.app :title="'Detail Pesanan – Agrilink'">
    <x-slot:sidebar>
        @include('farmer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">Pesanan #{{ $order->order_number }}</h1>
        <p class="text-gray-500 text-sm">Dari {{ $order->buyer->name ?? 'Pembeli' }} · {{ $order->created_at->translatedFormat('d F Y, H:i') }}</p>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h2 class="font-bold text-gray-800 mb-4">Item Pesanan</h2>
                <div class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between items-center py-3">
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">{{ $item->product_name }}</p>
                                <p class="text-xs text-gray-400">{{ $item->quantity }} {{ $item->unit }} × Rp {{ number_format($item->price_per_unit, 0, ',', '.') }}</p>
                            </div>
                            <p class="font-bold text-gray-800 text-sm">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="pt-4 mt-2 border-t border-gray-100 flex justify-between text-base font-bold">
                    <span class="text-gray-800">Total</span><span class="text-emerald-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h2 class="font-bold text-gray-800 mb-3">Alamat Pengiriman</h2>
                <p class="text-sm text-gray-600">{{ $order->shipping_address }}</p>
                @if ($order->buyer_notes)
                    <p class="text-xs text-gray-400 mt-2">Catatan pembeli: {{ $order->buyer_notes }}</p>
                @endif
            </div>
        </div>

        <div class="space-y-5">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h2 class="font-bold text-gray-800 mb-3">Status Pesanan</h2>
                <span class="bg-gray-100 text-gray-700 text-xs font-semibold px-2.5 py-1 rounded-md">{{ ucfirst($order->order_status) }}</span>

                @if ($order->order_status === 'pending')
                    <form method="POST" action="{{ route('farmer.orders.confirm', $order) }}" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">Konfirmasi Pesanan</button>
                    </form>
                @endif

                @if (in_array($order->order_status, ['confirmed', 'processing']))
                    <form method="POST" action="{{ route('farmer.orders.ship', $order) }}" class="mt-4 space-y-2">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="tracking_number" placeholder="Nomor resi (opsional)"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">Tandai Dikirim</button>
                    </form>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h2 class="font-bold text-gray-800 mb-3">Pembeli</h2>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-700 font-bold">{{ substr($order->buyer->name ?? 'P', 0, 1) }}</div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $order->buyer->name ?? 'Pembeli' }}</p>
                        <p class="text-xs text-gray-400">{{ $order->buyer->phone ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ route('chat.index', ['target' => $order->buyer_id]) }}" class="mt-3 block text-center bg-gray-50 hover:bg-emerald-50 text-emerald-700 font-semibold py-2 rounded-xl text-xs transition-colors">
                    Hubungi Pembeli
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
