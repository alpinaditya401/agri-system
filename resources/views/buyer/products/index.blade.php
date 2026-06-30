<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Pertanian - Agrilink</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
    <x-public-nav active="products" />

    <main class="pt-20">
        <section class="bg-white">
            <div class="ag-container py-12">
                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-end">
                    <div>
                        <p class="ag-label">Marketplace Petani</p>
                        <h1 class="mt-4 text-4xl font-black leading-none text-slate-950 md:text-6xl">Pasar Produk Pertanian</h1>
                        <p class="mt-5 max-w-2xl text-base leading-8 text-slate-500">
                            Cari hasil panen siap jual dari petani terverifikasi. Bandingkan harga, stok, dan lokasi asal sebelum membeli.
                        </p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase text-slate-500">Produk tersedia</p>
                                <p class="mt-1 text-3xl font-black text-emerald-700">{{ number_format($products->total(), 0, ',', '.') }}</p>
                            </div>
                            @auth
                                @if (auth()->user()->role?->name === 'buyer')
                                    <a href="{{ route('buyer.cart.index') }}" class="ag-btn-primary px-4 py-2">Keranjang</a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="ag-btn-secondary px-4 py-2">Masuk</a>
                            @endauth
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('products.index') }}" class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="grid gap-3 lg:grid-cols-[minmax(0,1.4fr)_220px_160px_160px_auto_auto]">
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Cari Produk</span>
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari beras, cabai, bawang..." class="ag-input">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Kategori</span>
                            <select name="category" class="ag-select">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Mulai</span>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="ag-input">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Sampai</span>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="ag-input">
                        </label>
                        <button type="submit" class="ag-btn-primary self-end">Cari</button>
                        <a href="{{ route('products.index') }}" class="ag-btn-secondary self-end">Reset</a>
                    </div>
                </form>

                <div class="mt-5 flex gap-2 overflow-x-auto pb-1 ag-no-scrollbar">
                    <a href="{{ route('products.index', array_filter(['search' => request('search'), 'start_date' => request('start_date'), 'end_date' => request('end_date')])) }}" class="ag-chip flex-shrink-0 {{ request('category') ? '' : 'ag-chip-active' }}">Semua</a>
                    @foreach ($categories->take(10) as $cat)
                        <a href="{{ route('products.index', array_filter(['category' => $cat->slug, 'search' => request('search'), 'start_date' => request('start_date'), 'end_date' => request('end_date')])) }}" class="ag-chip flex-shrink-0 {{ request('category') === $cat->slug ? 'ag-chip-active' : '' }}">{{ $cat->name }}</a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="ag-container py-10">
            @if ($products->isEmpty())
                <x-ui.empty-state title="Produk tidak ditemukan" message="Coba ubah kata kunci atau filter kategori.">
                    <a href="{{ route('products.index') }}" class="ag-btn-primary">Lihat Semua Produk</a>
                </x-ui.empty-state>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <a href="{{ route('products.show', $product->slug) }}" class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-950/8">
                            <div class="relative aspect-[4/3] overflow-hidden bg-emerald-50">
                                <x-product-visual :product="$product" class="h-full w-full" />
                                @if ($product->category)
                                    <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-[11px] font-black uppercase text-emerald-700 shadow-sm">{{ $product->category->name }}</span>
                                @endif
                            </div>

                            <div class="p-5">
                                <h2 class="line-clamp-2 min-h-12 text-base font-black leading-snug text-slate-950 group-hover:text-emerald-700">{{ $product->name }}</h2>
                                <p class="mt-3 text-2xl font-black text-emerald-700">
                                    Rp {{ number_format($product->price_per_unit, 0, ',', '.') }}
                                    <span class="text-sm font-bold text-slate-400">/{{ $product->unit }}</span>
                                </p>

                                <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="font-bold text-slate-400">Stok</p>
                                        <p class="mt-1 font-black text-slate-900">{{ number_format($product->stock_quantity, 0, ',', '.') }} {{ $product->unit }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-3">
                                        <p class="font-bold text-slate-400">Min. Order</p>
                                        <p class="mt-1 font-black text-slate-900">{{ number_format($product->minimum_order, 0, ',', '.') }} {{ $product->unit }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 border-t border-slate-100 pt-4">
                                    <p class="truncate text-sm font-bold text-slate-700">{{ $product->origin_district ?? $product->origin_province ?? 'Lokasi belum tersedia' }}</p>
                                    <p class="mt-1 truncate text-xs font-semibold text-slate-400">{{ $product->farmer->name ?? 'Petani' }}</p>
                                </div>

                                <span class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-700 transition group-hover:bg-emerald-600 group-hover:text-white">
                                    Lihat Detail
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs font-semibold text-slate-400">
        Agrilink - Sistem pertanian digital untuk distribusi, niaga, dan informasi harga.
    </footer>
</body>
</html>
