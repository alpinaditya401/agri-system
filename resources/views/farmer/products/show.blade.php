<x-layouts.app :title="$produk->name . ' – Agrilink'">
    <x-slot:sidebar>
        @include('farmer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">{{ $produk->name }}</h1>
        <p class="text-gray-500 text-sm">Detail produk Anda.</p>
    </x-slot:header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_380px]">
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <x-product-visual :product="$produk" class="aspect-[4/3]" icon-class="h-16 w-16" frame-class="h-24 w-24 rounded-3xl" image-class="h-full w-full object-cover" />
        </div>

        <aside class="self-start rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap gap-2">
                <span class="text-xs font-bold px-3 py-1 rounded-full {{ $produk->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($produk->status) }}
                </span>
                @if ($produk->category)
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">{{ $produk->category->name }}</span>
                @endif
            </div>
            <p class="mt-4 text-3xl font-extrabold text-emerald-700">Rp {{ number_format($produk->price_per_unit, 0, ',', '.') }}<span class="text-gray-400 font-medium text-base">/{{ $produk->unit }}</span></p>
            <div class="grid grid-cols-2 gap-3 mt-5 text-sm">
                <div class="rounded-xl bg-gray-50 p-4"><p class="text-xs text-gray-400">Stok</p><p class="font-extrabold text-gray-900">{{ number_format($produk->stock_quantity, 0, ',', '.') }} {{ $produk->unit }}</p></div>
                <div class="rounded-xl bg-gray-50 p-4"><p class="text-xs text-gray-400">Min. Order</p><p class="font-extrabold text-gray-900">{{ number_format($produk->minimum_order, 0, ',', '.') }} {{ $produk->unit }}</p></div>
                <div class="rounded-xl bg-gray-50 p-4"><p class="text-xs text-gray-400">Kategori</p><p class="font-extrabold text-gray-900">{{ $produk->category->name ?? '-' }}</p></div>
                <div class="rounded-xl bg-gray-50 p-4"><p class="text-xs text-gray-400">Asal</p><p class="font-extrabold text-gray-900">{{ $produk->origin_district ?? '-' }}</p></div>
            </div>
            <p class="text-sm text-gray-600 mt-4">{{ $produk->description ?: 'Tidak ada deskripsi.' }}</p>

            <div class="grid gap-3 mt-5 sm:grid-cols-2 lg:grid-cols-1">
                <a href="{{ route('farmer.produk.edit', $produk) }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-bold text-white transition-colors hover:bg-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    Edit Produk
                </a>
                <a href="{{ route('products.show', $produk->slug) }}" target="_blank" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-gray-100 px-4 text-sm font-bold text-gray-700 transition-colors hover:bg-gray-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5M21 3l-9 9m0 0h5.25M12 12V6.75"/></svg>
                    Lihat di Marketplace
                </a>
            </div>
        </aside>
    </div>
</x-layouts.app>
