<x-layouts.app :title="'Keranjang - Agrilink'">
    <x-slot:sidebar>
        @include('buyer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading flex items-center gap-2">
            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9Z" /></svg>
            Keranjang Belanja
        </h1>
        <p class="mt-1 text-sm text-slate-500">Periksa kembali pesanan Anda sebelum checkout.</p>
    </x-slot:header>

    @if ($errors->has('checkout'))
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ $errors->first('checkout') }}
        </div>
    @endif

    @if ($cartItems->isEmpty())
        <x-ui.empty-state title="Keranjang Anda masih kosong" message="Tambahkan produk dari marketplace untuk mulai checkout.">
            <a href="{{ route('products.index') }}" class="ag-btn-primary">Belanja Sekarang</a>
        </x-ui.empty-state>
    @else
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="space-y-4">
                @foreach ($cartItems as $item)
                    @php
                        $lineSubtotal = ($item->product->price_per_unit ?? 0) * $item->quantity;
                    @endphp
                    <div class="ag-card p-4">
                        <div class="grid gap-4 md:grid-cols-[84px_minmax(0,1fr)_auto_auto] md:items-center">
                            <div class="h-20 w-20 overflow-hidden rounded-2xl bg-emerald-50">
                                @if ($item->product?->main_image_url)
                                    <img src="{{ $item->product->main_image_url }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover">
                                @else
                                    <x-product-visual :product="$item->product" class="h-full w-full" icon-class="h-8 w-8" frame-class="h-12 w-12 rounded-2xl" />
                                @endif
                            </div>

                            <div class="min-w-0">
                                <p class="font-black text-slate-950">{{ $item->product->name ?? 'Produk tidak tersedia' }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-500">{{ $item->product->farmer->name ?? '-' }}</p>
                                <p class="mt-2 text-sm font-black text-emerald-700">Rp {{ number_format($item->product->price_per_unit ?? 0, 0, ',', '.') }}/{{ $item->product->unit ?? '' }}</p>
                            </div>

                            <form method="POST" action="{{ route('buyer.cart.update', $item) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <label class="sr-only" for="quantity-{{ $item->id }}">Jumlah</label>
                                <input id="quantity-{{ $item->id }}" type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="w-20 rounded-xl border border-slate-200 px-3 py-2 text-center text-sm font-bold outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                                <button type="submit" class="ag-btn-secondary min-h-10 px-3 py-2 text-xs" data-loading-text="Update...">Update</button>
                            </form>

                            <div class="flex items-center justify-between gap-3 md:block md:text-right">
                                <div>
                                    <p class="text-xs font-bold uppercase text-slate-400">Subtotal</p>
                                    <p class="mt-1 font-black text-slate-950">Rp {{ number_format($lineSubtotal, 0, ',', '.') }}</p>
                                </div>
                                <form method="POST" action="{{ route('buyer.cart.remove', $item) }}" data-confirm="Hapus produk ini dari keranjang?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600 transition hover:bg-red-100" aria-label="Hapus produk">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 7-.9 12.1A2 2 0 0 1 16.1 21H7.9a2 2 0 0 1-2-1.9L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="ag-card h-fit p-5 xl:sticky xl:top-24">
                <h2 class="text-xl font-black text-slate-950">Ringkasan Checkout</h2>
                <div class="mt-5 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="font-semibold text-slate-500">Subtotal</span>
                        <span class="font-black text-slate-900">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="font-semibold text-slate-500">Ongkos kirim</span>
                        <span class="text-right">
                            <span class="block font-black text-emerald-700">Gratis</span>
                            <span class="block text-[11px] font-semibold text-slate-400">Mode demo, belum dihitung otomatis</span>
                        </span>
                    </div>
                    <div class="flex justify-between border-t border-slate-100 pt-4 text-lg font-black">
                        <span>Total</span>
                        <span class="text-emerald-700">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('buyer.cart.checkout') }}" class="mt-6 space-y-4">
                    @csrf
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Alamat Pengiriman</span>
                        <textarea name="address" rows="3" required placeholder="Tulis alamat lengkap..." class="ag-input resize-none">{{ old('address', auth()->user()->address) }}</textarea>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Catatan (opsional)</span>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: titip ke pos satpam" class="ag-input">
                    </label>
                    <button type="submit" class="ag-btn-primary w-full rounded-2xl py-4" data-loading-text="Checkout...">
                        Checkout Sekarang
                    </button>
                </form>
            </aside>
        </div>
    @endif
</x-layouts.app>
