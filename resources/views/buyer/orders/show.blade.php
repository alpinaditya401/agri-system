<x-layouts.app :title="'Detail Pesanan - Agrilink'">
    <x-slot:sidebar>
        @include('buyer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading">Pesanan #{{ $order->order_number }}</h1>
        <p class="mt-1 text-sm text-slate-500">Dibuat {{ $order->created_at->translatedFormat('d F Y, H:i') }}</p>
    </x-slot:header>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="space-y-5">
            <x-ui.card>
                <h2 class="text-xl font-black text-slate-950">Item Pesanan</h2>
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($order->items as $item)
                        <div class="flex items-start justify-between gap-4 py-4">
                            <div>
                                <p class="font-black text-slate-900">{{ $item->product_name }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-500">{{ $item->quantity }} {{ $item->unit }} x Rp {{ number_format($item->price_per_unit, 0, ',', '.') }}</p>
                            </div>
                            <p class="whitespace-nowrap font-black text-slate-950">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 space-y-2 border-t border-slate-100 pt-4">
                    <div class="flex justify-between text-sm"><span class="font-semibold text-slate-500">Subtotal</span><span class="font-black text-slate-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-sm"><span class="font-semibold text-slate-500">Ongkos Kirim</span><span class="font-black text-slate-900">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between pt-3 text-lg font-black"><span>Total</span><span class="text-emerald-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span></div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-xl font-black text-slate-950">Alamat Pengiriman</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $order->shipping_address }}</p>
                @if ($order->tracking_number)
                    <p class="mt-3 text-sm text-slate-500">No. Resi: <span class="font-black text-slate-900">{{ $order->tracking_number }}</span></p>
                @endif
                @if ($order->buyer_notes)
                    <p class="mt-3 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">Catatan: {{ $order->buyer_notes }}</p>
                @endif
            </x-ui.card>
        </div>

        <aside class="space-y-5">
            <x-ui.card>
                <h2 class="text-xl font-black text-slate-950">Status</h2>
                <div class="mt-5 space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-slate-500">Pesanan</span>
                        <x-ui.badge :tone="$order->order_status">{{ ucfirst($order->order_status) }}</x-ui.badge>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-slate-500">Pembayaran</span>
                        <x-ui.badge :tone="$order->payment_status">{{ ucfirst($order->payment_status) }}</x-ui.badge>
                    </div>
                    @if ($order->payment_gateway)
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm font-semibold text-slate-500">Gateway</span>
                            <x-ui.badge tone="muted">{{ ucfirst($order->payment_gateway) }}</x-ui.badge>
                        </div>
                    @endif
                </div>

                @if ($order->payment_status === 'pending' && $order->order_status !== 'cancelled')
                    <form method="POST" action="{{ route('buyer.orders.pay', $order) }}" class="mt-5">
                        @csrf
                        <button type="submit" class="ag-btn-primary w-full rounded-2xl" data-loading-text="Membuka pembayaran...">
                            Bayar Sekarang
                        </button>
                    </form>
                    @if ($order->payment_expires_at)
                        <p class="mt-3 text-center text-xs font-semibold text-slate-400">
                            Link pembayaran berlaku sampai {{ $order->payment_expires_at->translatedFormat('d M Y, H:i') }}.
                        </p>
                    @endif
                @endif

                @if ($order->order_status === 'delivered')
                    <form method="POST" action="{{ route('buyer.orders.complete', $order) }}" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="ag-btn-primary w-full rounded-2xl" data-loading-text="Menyelesaikan...">Selesaikan Pesanan</button>
                    </form>
                @endif

                @if (in_array($order->order_status, ['pending', 'confirmed']))
                    <form method="POST" action="{{ route('buyer.orders.cancel', $order) }}" class="mt-3" data-confirm="Batalkan pesanan ini?">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="ag-btn-danger w-full rounded-2xl bg-red-50 text-red-600 hover:bg-red-100" data-loading-text="Membatalkan...">Batalkan Pesanan</button>
                    </form>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-xl font-black text-slate-950">Penjual</h2>
                <div class="mt-4 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-sm font-black text-emerald-700">{{ substr($order->farmer->name ?? 'P', 0, 1) }}</div>
                    <div class="min-w-0">
                        <p class="truncate font-black text-slate-900">{{ $order->farmer->name ?? 'Petani' }}</p>
                        <p class="text-sm font-semibold text-slate-400">{{ $order->farmer->district ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ route('chat.index', ['target' => $order->farmer_id]) }}" class="ag-btn-secondary mt-4 w-full rounded-2xl">
                    Hubungi Penjual
                </a>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
