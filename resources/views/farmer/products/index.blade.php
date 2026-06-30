<x-layouts.app :title="'Produk Saya – Agrilink'">
    <x-slot:sidebar>
        @include('farmer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            Produk Saya
        </h1>
        <p class="mt-1 text-sm text-slate-500">Kelola produk pertanian yang Anda jual.</p>
    </x-slot:header>

    @php
        $totalProducts = $products->total();
        $visibleProducts = $products->getCollection();
        $activeProducts = $visibleProducts->where('status', 'active')->count();
        $draftProducts = $visibleProducts->where('status', 'draft')->count();
        $stockTotal = $visibleProducts->sum('stock_quantity');
    @endphp

    <div class="mb-5 grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_auto]">
        <div class="grid grid-cols-3 gap-3">
            <div class="ag-card p-4">
                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Total produk</p>
                <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ number_format($totalProducts, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-700">Aktif</p>
                <p class="mt-1 text-2xl font-extrabold text-emerald-800">{{ number_format($activeProducts, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wide text-blue-700">Stok halaman ini</p>
                <p class="mt-1 text-2xl font-extrabold text-blue-800">{{ number_format($stockTotal, 0, ',', '.') }}</p>
            </div>
        </div>
        <a href="{{ route('farmer.produk.create') }}" class="ag-btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Produk
        </a>
    </div>

    <div class="ag-card overflow-hidden" style="padding:0">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-3">
            <p class="text-sm font-bold text-gray-800">Daftar produk</p>
            <p class="text-xs text-gray-500">Kelola status, stok, dan harga produk yang tampil di marketplace.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">Produk</th>
                        <th class="p-4 font-semibold">Kategori</th>
                        <th class="p-4 font-semibold">Harga</th>
                        <th class="p-4 font-semibold">Stok</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($products as $product)
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 flex items-center gap-3">
                                <x-product-visual :product="$product" class="h-12 w-12 flex-shrink-0 rounded-xl" icon-class="h-5 w-5" frame-class="h-9 w-9 rounded-xl" />
                                <div>
                                    <span class="font-bold text-gray-800">{{ $product->name }}</span>
                                    <p class="text-xs text-gray-400">{{ $product->origin_district ?? $product->origin_province ?? '-' }}</p>
                                </div>
                            </td>
                            <td class="p-4 text-gray-600">{{ $product->category->name ?? '-' }}</td>
                            <td class="p-4 text-gray-700 font-semibold">Rp {{ number_format($product->price_per_unit, 0, ',', '.') }}<span class="text-xs font-normal text-gray-400">/{{ $product->unit }}</span></td>
                            <td class="p-4 text-gray-700">{{ number_format($product->stock_quantity, 0, ',', '.') }} {{ $product->unit }}</td>
                            <td class="p-4">
                                <x-ui.badge :tone="$product->status">{{ ucfirst($product->status) }}</x-ui.badge>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    <a href="{{ route('farmer.produk.show', $product) }}" class="text-gray-500 hover:text-gray-800 font-bold text-xs">Lihat</a>
                                    <a href="{{ route('farmer.produk.edit', $product) }}" class="text-emerald-600 hover:text-emerald-800 font-bold text-xs">Edit</a>
                                    <form method="POST" action="{{ route('farmer.produk.toggle-status', $product) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-amber-600 hover:text-amber-800 font-bold text-xs">{{ $product->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('farmer.produk.destroy', $product) }}" data-confirm="Hapus produk ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                </div>
                                <p class="mt-4 font-bold text-gray-800">Belum ada produk</p>
                                <p class="mt-1 text-sm text-gray-500">Tambahkan produk pertama agar pembeli bisa menemukannya di marketplace.</p>
                                <a href="{{ route('farmer.produk.create') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Tambah Produk</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $products->links() }}
    </div>
</x-layouts.app>
