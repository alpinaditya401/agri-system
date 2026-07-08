<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - Agrilink</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
    <x-public-nav active="products" />

    <main class="pt-20">
        <section class="ag-container py-8">
            <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-emerald-700">Beranda</a>
                <span>/</span>
                <a href="{{ route('products.index') }}" class="hover:text-emerald-700">Produk</a>
                <span>/</span>
                <span class="max-w-xs truncate text-slate-900">{{ $product->name }}</span>
            </nav>

            @if (session('success'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
                    {{ session('success') }}
                    @auth
                        @if (auth()->user()->role?->name === 'buyer')
                            <a href="{{ route('buyer.cart.index') }}" class="ml-2 font-black underline underline-offset-4">Lihat keranjang</a>
                        @endif
                    @endauth
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_460px]">
                <div class="space-y-4">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-3 shadow-sm">
                        <x-product-visual :product="$product" class="aspect-[4/3] rounded-[1.5rem]" icon-class="h-16 w-16" frame-class="h-24 w-24 rounded-3xl" image-class="h-full w-full object-cover" />
                    </div>

                    @if ($product->images->isNotEmpty())
                        <div class="flex gap-3 overflow-x-auto pb-1 ag-no-scrollbar">
                            @foreach ($product->images as $img)
                                @if ($img->image_url)
                                    <img src="{{ $img->image_url }}" alt="{{ $product->name }}" loading="lazy" class="h-20 w-20 flex-shrink-0 rounded-2xl border border-slate-200 object-cover shadow-sm" onerror="this.remove()">
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="ag-card p-6">
                        <h2 class="text-lg font-black text-slate-950">Deskripsi Produk</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $product->description ?: 'Tidak ada deskripsi.' }}</p>
                    </div>
                </div>

                <aside class="space-y-4">
                    <div class="ag-card p-6 md:p-7">
                        @if ($product->category)
                            <x-ui.badge tone="success">{{ $product->category->name }}</x-ui.badge>
                        @endif

                        <h1 class="mt-4 text-3xl font-black leading-tight text-slate-950 md:text-4xl">{{ $product->name }}</h1>
                        <p class="mt-5 text-4xl font-black text-emerald-700">
                            Rp {{ number_format($product->price_per_unit, 0, ',', '.') }}
                            <span class="text-base font-bold text-slate-400">/{{ $product->unit }}</span>
                        </p>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Stok</p>
                                <p class="mt-2 text-lg font-black text-slate-950">{{ number_format($product->stock_quantity, 0, ',', '.') }} {{ $product->unit }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Min. Order</p>
                                <p class="mt-2 text-lg font-black text-slate-950">{{ number_format($product->minimum_order, 0, ',', '.') }} {{ $product->unit }}</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3 rounded-3xl border border-slate-200 bg-white p-4">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z" /></svg>
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase text-slate-400">Petani</p>
                                    <p class="font-black text-slate-950">{{ $product->farmer->name ?? 'Petani' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.7 7-11a7 7 0 1 0-14 0c0 6.3 7 11 7 11Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10.5h.01" /></svg>
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase text-slate-400">Lokasi Asal</p>
                                    <p class="font-black text-slate-950">{{ $product->origin_district ?? $product->origin_province ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        @auth
                            <form method="POST" action="{{ route('buyer.cart.add') }}" class="mt-6 space-y-3" data-add-cart-form>
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <label class="block">
                                    <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Jumlah Pembelian</span>
                                    <input type="number" name="quantity" min="{{ $product->minimum_order }}" max="{{ $product->stock_quantity }}" value="{{ old('quantity', $product->minimum_order) }}" class="ag-input">
                                </label>
                                <button type="submit" class="ag-btn-primary w-full rounded-2xl py-4 text-base" data-loading-text="Menambahkan...">
                                    Tambah ke Keranjang
                                </button>
                            </form>

                            @if ($product->farmer_id && $product->farmer_id !== auth()->id())
                                <a href="{{ route('chat.index', ['target' => $product->farmer_id]) }}" class="ag-btn-secondary mt-3 w-full rounded-2xl py-4">
                                    Chat Penjual
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="ag-btn-primary mt-6 w-full rounded-2xl py-4 text-base">
                                Masuk untuk Membeli
                            </a>
                        @endauth
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        @foreach ([
                            ['title' => 'Petani terdaftar', 'desc' => 'Profil penjual terhubung dengan sistem Agrilink.'],
                            ['title' => 'Stok tersedia', 'desc' => 'Jumlah stok terlihat sebelum checkout.'],
                            ['title' => 'Lokasi asal jelas', 'desc' => 'Produk menampilkan asal wilayah petani.'],
                        ] as $trust)
                            <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                                <p class="font-black text-slate-950">{{ $trust['title'] }}</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ $trust['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </aside>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs font-semibold text-slate-400">
        Agrilink - Sistem pertanian digital untuk distribusi, niaga, dan informasi harga.
    </footer>
</body>
</html>
