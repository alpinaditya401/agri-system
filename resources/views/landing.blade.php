<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agrilink - Platform Pertanian Digital Indonesia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <x-public-nav active="home" />

    @php
        $heroVideoExists = file_exists(public_path('videos/hero-agriculture.mp4'));
        $quickFeatures = [
            ['title' => 'Harga Komoditas', 'desc' => 'Pantau harga acuan dan referensi BPS dalam satu halaman.', 'url' => route('public.prices'), 'tone' => 'emerald'],
            ['title' => 'Marketplace Hasil Tani', 'desc' => 'Jelajahi produk petani aktif, stok, harga, dan lokasi asal.', 'url' => route('products.index'), 'tone' => 'lime'],
            ['title' => 'Pupuk Bersubsidi', 'desc' => 'Akses alur pengajuan dan distribusi pupuk sesuai role pengguna.', 'url' => auth()->check() ? route('dashboard') : route('login'), 'tone' => 'amber'],
            ['title' => 'Peta Distribusi', 'desc' => 'Lihat sebaran petani, distributor, dan komoditas di Indonesia.', 'url' => route('public.map'), 'tone' => 'sky'],
        ];
    @endphp

    <main>
        <section class="relative overflow-hidden bg-emerald-950 pt-28 text-white">
            <div class="absolute inset-0">
                @if ($heroVideoExists)
                    <video autoplay muted loop playsinline preload="metadata" poster="{{ asset('images/landing/sawah.webp') }}" class="h-full w-full object-cover opacity-45">
                        <source src="{{ asset('videos/hero-agriculture.mp4') }}" type="video/mp4">
                    </video>
                @else
                    <img src="{{ asset('images/landing/sawah.webp') }}" alt="Lanskap pertanian Indonesia" class="h-full w-full object-cover opacity-40">
                @endif
            </div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(52,211,153,.30),transparent_32%),linear-gradient(135deg,rgba(2,44,34,.96),rgba(6,78,59,.90)_50%,rgba(20,83,45,.94))]"></div>

            <div class="ag-container relative z-10 grid min-h-[680px] items-center gap-10 pb-20 lg:grid-cols-[minmax(0,0.95fr)_minmax(340px,460px)] lg:gap-14">
                <div class="max-w-3xl" data-reveal>
                    <p class="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-2 text-[11px] font-bold uppercase text-emerald-100 backdrop-blur-xl tracking-[0.22em]">
                        Platform Pertanian Digital Indonesia
                    </p>
                    <h1 class="ag-display mt-7 text-[clamp(2.9rem,6.4vw,5.9rem)] font-black leading-[0.92] text-white ag-text-balance">
                        Hubungkan Petani, Pembeli, dan Distributor
                    </h1>
                    <p class="mt-6 max-w-2xl text-base leading-8 text-emerald-50/82 md:text-lg">
                        Pantau harga komoditas, jual hasil tani, ajukan pupuk subsidi, kelola transaksi, dan lihat peta distribusi pertanian secara lebih mudah.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('products.index') }}" class="ag-btn-primary rounded-2xl px-6 py-4 text-base">
                            Jelajahi Produk
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0-4 4m4-4H3" />
                            </svg>
                        </a>
                        <a href="{{ route('public.prices') }}" class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-white/20 bg-white/10 px-6 py-4 text-base font-bold text-white backdrop-blur-xl transition hover:bg-white/15">
                            Cek Harga Komoditas
                        </a>
                    </div>

                    <div class="mt-7 flex flex-wrap gap-2 text-xs font-bold text-emerald-50/85">
                        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5">Petani</span>
                        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5">Pembeli</span>
                        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5">Distributor</span>
                        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5">Admin Daerah</span>
                    </div>
                </div>

                <div class="mx-auto w-full max-w-[460px]" data-reveal>
                    <div class="ag-glass rounded-[2rem] p-3 md:p-4">
                        <div class="rounded-[1.5rem] border border-white/10 bg-emerald-950/60 p-5 shadow-2xl">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-[11px] font-bold uppercase text-emerald-200 tracking-[0.18em]">Dashboard Preview</p>
                                    <h2 class="mt-2 text-2xl font-black text-white">Ekosistem Agrilink</h2>
                                </div>
                                <span class="rounded-full bg-amber-300 px-3 py-1 text-xs font-black text-emerald-950">Live</span>
                            </div>

                            <div class="mt-6 grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-white p-4 text-slate-900">
                                    <p class="text-[11px] font-bold uppercase text-slate-500">Harga</p>
                                    <p class="mt-2 text-2xl font-black text-emerald-700">{{ number_format(collect($latestPrices ?? [])->count(), 0, ',', '.') }}</p>
                                    <p class="mt-1 text-xs text-slate-500">referensi aktif</p>
                                </div>
                                <div class="rounded-2xl bg-white/12 p-4 ring-1 ring-white/15">
                                    <p class="text-[11px] font-bold uppercase text-emerald-100/70">Produk</p>
                                    <p class="mt-2 text-xl font-black text-white">Marketplace</p>
                                    <p class="mt-1 text-xs text-emerald-50/60">hasil tani siap jual</p>
                                </div>
                                <div class="rounded-2xl bg-white/12 p-4 ring-1 ring-white/15">
                                    <p class="text-[11px] font-bold uppercase text-emerald-100/70">Pupuk</p>
                                    <p class="mt-2 text-xl font-black text-white">Terkelola</p>
                                    <p class="mt-1 text-xs text-emerald-50/60">subsidi transparan</p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 text-slate-900">
                                    <p class="text-[11px] font-bold uppercase text-slate-500">Peta</p>
                                    <p class="mt-2 text-2xl font-black text-emerald-700">Indonesia</p>
                                    <p class="mt-1 text-xs text-slate-500">sebaran aktif</p>
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl border border-amber-200/30 bg-amber-300/15 p-4">
                                <p class="text-sm font-bold text-amber-100">Pengajuan dan transaksi lebih mudah dipantau</p>
                                <p class="mt-1 text-xs leading-5 text-amber-50/70">Setiap role membuka dashboard yang sesuai dengan pekerjaan utamanya.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-slate-50 py-8 md:py-10">
            <div class="ag-container">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($quickFeatures as $index => $card)
                        <a href="{{ $card['url'] }}" class="group flex min-h-44 flex-col justify-between rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-xl hover:shadow-emerald-950/8" data-reveal style="--reveal-delay: {{ $index * 70 }}ms">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl {{ $card['tone'] === 'emerald' ? 'bg-emerald-50 text-emerald-700' : ($card['tone'] === 'lime' ? 'bg-lime-50 text-lime-700' : ($card['tone'] === 'amber' ? 'bg-amber-50 text-amber-700' : 'bg-sky-50 text-sky-700')) }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.5c5.2-6.8 12-7.4 15.5-4.4-2.1 5.9-9 8.8-15.5 4.4Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.7 12.5c5.1.2 9.7-1.4 13.4-4.3" />
                                </svg>
                            </span>
                            <span>
                                <span class="block text-base font-black text-slate-950">{{ $card['title'] }}</span>
                                <span class="mt-2 block text-sm leading-6 text-slate-500">{{ $card['desc'] }}</span>
                            </span>
                            <span class="mt-5 inline-flex items-center gap-2 text-sm font-black text-emerald-700">
                                Buka halaman
                                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0-4 4m4-4H3" />
                                </svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="ag-section">
            <div class="ag-container">
                <div class="max-w-3xl" data-reveal>
                    <p class="ag-label">Fitur Utama</p>
                    <h2 class="ag-heading-xl mt-4 normal-case">Semua kebutuhan pertanian dalam satu platform</h2>
                    <p class="mt-5 text-lg leading-8 text-slate-500">
                        Agrilink merapikan proses niaga, informasi harga, pupuk subsidi, chat, artikel, dan peta distribusi dalam workflow yang mudah dipahami.
                    </p>
                </div>

                <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        ['title' => 'Harga Komoditas', 'desc' => 'Pantau perkembangan harga pertanian dari sumber data terpercaya.'],
                        ['title' => 'Marketplace Produk Tani', 'desc' => 'Petani dapat memasarkan hasil panen dan pembeli dapat membandingkan produk.'],
                        ['title' => 'Pupuk Bersubsidi', 'desc' => 'Pengajuan, kuota, distributor, dan riwayat subsidi tersusun rapi.'],
                        ['title' => 'Peta Distribusi', 'desc' => 'Lihat sebaran petani, distributor, dan produk aktif di wilayah pertanian.'],
                        ['title' => 'Chat dan Notifikasi', 'desc' => 'Percakapan dan update status transaksi tetap mudah dipantau.'],
                        ['title' => 'Artikel Edukasi', 'desc' => 'Publikasi informasi pertanian dan sumber data BPS dalam satu tempat.'],
                    ] as $index => $feature)
                        <div class="ag-card p-6 transition duration-300 hover:-translate-y-1 hover:shadow-md" data-reveal style="--reveal-delay: {{ ($index % 3) * 70 }}ms">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 12.5c5.2-6.8 12-7.4 15.5-4.4-2.1 5.9-9 8.8-15.5 4.4Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.7 12.5c5.1.2 9.7-1.4 13.4-4.3" />
                                </svg>
                            </div>
                            <h3 class="mt-5 text-xl font-black text-slate-950">{{ $feature['title'] }}</h3>
                            <p class="mt-3 text-sm leading-6 text-slate-500">{{ $feature['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="ag-section bg-white">
            <div class="ag-container">
                <div class="grid gap-10 lg:grid-cols-[360px_minmax(0,1fr)]">
                    <div data-reveal>
                        <p class="ag-label">Peran Pengguna</p>
                        <h2 class="ag-heading mt-4">Dirancang untuk setiap peran</h2>
                        <p class="mt-4 text-sm leading-7 text-slate-500">
                            Setiap role punya dashboard dan alur kerja yang spesifik, sehingga fitur tetap fokus dan tidak membingungkan.
                        </p>
                        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-slate-100 shadow-sm">
                            <img src="{{ asset('images/landing/petani.webp') }}" alt="Petani Indonesia di lahan pertanian" loading="lazy" class="aspect-[4/3] w-full object-cover">
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ([
                            'Petani' => ['Kelola produk', 'Pantau pesanan', 'Ajukan pupuk subsidi'],
                            'Pembeli' => ['Cari produk tani', 'Kelola keranjang', 'Pantau status pesanan'],
                            'Distributor' => ['Kelola stok pupuk', 'Proses pengajuan', 'Pantau riwayat distribusi'],
                            'Admin' => ['Verifikasi petani', 'Kelola pengguna', 'Pantau laporan'],
                        ] as $role => $items)
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6" data-reveal>
                                <h3 class="text-lg font-black text-slate-950">{{ $role }}</h3>
                                <ul class="mt-4 space-y-3">
                                    @foreach ($items as $item)
                                        <li class="flex items-center gap-3 text-sm font-semibold text-slate-600">
                                            <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m5 13 4 4L19 7" /></svg>
                                            </span>
                                            {{ $item }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="ag-section">
            <div class="ag-container grid gap-6 lg:grid-cols-2">
                <div class="ag-card p-6 md:p-8" data-reveal>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="ag-label">Harga Komoditas</p>
                            <h2 class="mt-3 text-2xl font-black text-slate-950">Preview harga terbaru</h2>
                        </div>
                        <a href="{{ route('public.prices') }}" class="ag-btn-secondary px-4 py-2">Lihat harga</a>
                    </div>
                    <div class="mt-6 divide-y divide-slate-100">
                        @forelse (collect($latestPrices ?? [])->take(5) as $price)
                            <div class="flex items-center justify-between gap-4 py-4">
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-900">{{ data_get($price, 'commodity_name', data_get($price, 'name', 'Komoditas')) }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ data_get($price, 'region', 'Nasional') }}</p>
                                </div>
                                <p class="shrink-0 text-right text-lg font-black text-emerald-700">
                                    Rp {{ number_format((float) data_get($price, 'price', 0), 0, ',', '.') }}
                                </p>
                            </div>
                        @empty
                            <x-ui.empty-state title="Data harga belum tersedia" message="Harga komoditas akan tampil setelah sinkronisasi data tersedia." class="mt-6" />
                        @endforelse
                    </div>
                </div>

                <div class="ag-card p-6 md:p-8" data-reveal>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="ag-label">Artikel Terbaru</p>
                            <h2 class="mt-3 text-2xl font-black text-slate-950">Edukasi pertanian</h2>
                        </div>
                        <a href="{{ route('public.articles') }}" class="ag-btn-secondary px-4 py-2">Baca artikel</a>
                    </div>
                    <div class="mt-6 grid gap-4">
                        @forelse (($articles ?? collect())->take(3) as $article)
                            @php($coverImage = $article->cover_image ?: 'images/articles/distribusi-pertanian.webp')
                            <a href="{{ route('public.articles.show', $article->slug) }}" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-emerald-200 hover:bg-emerald-50 sm:grid-cols-[112px_minmax(0,1fr)]">
                                @if ($coverImage && file_exists(public_path($coverImage)))
                                    <img src="{{ asset($coverImage) }}" alt="{{ $article->title }}" loading="lazy" class="h-28 w-full rounded-xl object-cover sm:h-full">
                                @endif
                                <span class="min-w-0 p-1">
                                    @if ($article->category)
                                        <span class="text-xs font-bold uppercase text-emerald-700">{{ $article->category }}</span>
                                    @endif
                                    <span class="mt-2 block line-clamp-2 font-black text-slate-950">{{ $article->title }}</span>
                                    <span class="mt-2 block line-clamp-2 text-sm leading-6 text-slate-500">{{ $article->excerpt }}</span>
                                </span>
                            </a>
                        @empty
                            <x-ui.empty-state title="Artikel belum tersedia" message="Artikel edukasi akan tampil setelah dipublikasikan admin." class="mt-6" />
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="px-4 pb-16">
            <div class="mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-emerald-950 p-8 text-white shadow-2xl shadow-emerald-950/20 md:p-12" data-reveal>
                <div class="grid items-center gap-8 lg:grid-cols-[minmax(0,1fr)_auto]">
                    <div>
                        <p class="text-xs font-bold uppercase text-emerald-200 tracking-[0.22em]">Mulai Sekarang</p>
                        <h2 class="mt-4 text-3xl font-black md:text-5xl">Bangun ekosistem pertanian yang lebih tertata.</h2>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-emerald-50/75">
                            Gunakan Agrilink untuk menghubungkan informasi harga, transaksi hasil tani, distribusi pupuk, dan data lapangan dalam satu platform.
                        </p>
                    </div>
                    <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-white px-6 py-4 text-sm font-black text-emerald-950 transition hover:bg-emerald-50">
                        {{ auth()->check() ? 'Buka Dashboard' : 'Daftar Sekarang' }}
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="ag-container grid gap-8 py-10 md:grid-cols-4">
            <div>
                <h2 class="text-xl font-black text-slate-950">Agrilink</h2>
                <p class="mt-3 text-sm leading-6 text-slate-500">Platform pertanian digital untuk harga, niaga, pupuk, dan peta distribusi.</p>
            </div>
            <div>
                <p class="font-black text-slate-950">Platform</p>
                <div class="mt-3 grid gap-2 text-sm text-slate-500">
                    <a href="{{ route('public.prices') }}" class="hover:text-emerald-700">Harga Komoditas</a>
                    <a href="{{ route('products.index') }}" class="hover:text-emerald-700">Produk</a>
                    <a href="{{ route('public.map') }}" class="hover:text-emerald-700">Peta</a>
                </div>
            </div>
            <div>
                <p class="font-black text-slate-950">Dashboard</p>
                <div class="mt-3 grid gap-2 text-sm text-slate-500">
                    <a href="{{ route('login') }}" class="hover:text-emerald-700">Masuk</a>
                    <a href="{{ route('register') }}" class="hover:text-emerald-700">Daftar</a>
                    <a href="{{ route('dashboard') }}" class="hover:text-emerald-700">Dashboard</a>
                </div>
            </div>
            <div>
                <p class="font-black text-slate-950">Bantuan</p>
                <div class="mt-3 grid gap-2 text-sm text-slate-500">
                    <a href="{{ route('public.articles') }}" class="hover:text-emerald-700">Artikel</a>
                    <a href="{{ route('public.articles.show', 'sumber-data-bps-agrilink') }}" class="hover:text-emerald-700">Sumber Data BPS</a>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-100 py-5 text-center text-xs font-semibold text-slate-400">
            &copy; 2026 Agrilink. Semua hak dilindungi.
        </div>
    </footer>
</body>
</html>
