<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel - Agrilink</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
    <x-public-nav active="articles" />

    <main class="pt-20">
        <section class="bg-white">
            <div class="ag-container py-14">
                <p class="ag-label">Pusat Edukasi</p>
                <div class="mt-4 grid gap-6 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-end">
                    <div>
                        <h1 class="text-4xl font-black leading-none text-slate-950 md:text-6xl">Artikel Pertanian</h1>
                        <p class="mt-5 max-w-2xl text-base leading-8 text-slate-500">
                            Tips, kebijakan, sumber data, dan kabar terbaru seputar pertanian digital Indonesia.
                        </p>
                    </div>
                    <form method="GET" action="{{ route('public.articles') }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Cari Artikel</span>
                            <input type="search" name="search" value="{{ $search ?? request('search') }}" placeholder="Cari judul, tips, kebijakan..." class="ag-input">
                        </label>
                        <div class="mt-3 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto_auto]">
                            <label class="block">
                                <span class="sr-only">Kategori</span>
                                <select name="category" class="ag-select">
                                    <option value="">Semua Kategori</option>
                                    @foreach (($categories ?? collect()) as $cat)
                                        <option value="{{ $cat }}" {{ ($category ?? request('category')) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button type="submit" class="ag-btn-primary px-5 py-3">Cari</button>
                            <a href="{{ route('public.articles') }}" class="ag-btn-secondary px-5 py-3">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section class="ag-container py-10">
            @if ($articles->isEmpty())
                <x-ui.empty-state title="Artikel tidak ditemukan" message="Coba ubah kata kunci atau filter kategori." />
            @else
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($articles as $article)
                        @php
                            $fallbackCovers = [
                                'harga' => 'images/commodities/cabai-merah.webp',
                                'marketplace' => 'images/landing/petani.webp',
                                'pupuk' => 'images/landing/sawah.webp',
                                'edukasi' => 'images/commodities/gabah.webp',
                                'distribusi' => 'images/articles/distribusi-pertanian.webp',
                            ];
                            $coverImage = $article->cover_image ?: ($fallbackCovers[$article->category] ?? 'images/articles/distribusi-pertanian.webp');
                            $webpCover = preg_replace('/\.(png|jpe?g)$/i', '.webp', $coverImage);
                            if ($webpCover && file_exists(public_path($webpCover))) {
                                $coverImage = $webpCover;
                            }
                        @endphp
                        <a href="{{ route('public.articles.show', $article->slug) }}" class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-950/8">
                            <div class="relative h-48 overflow-hidden bg-emerald-50">
                                @if ($coverImage && file_exists(public_path($coverImage)))
                                    <img src="{{ asset($coverImage) }}" alt="{{ $article->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-50 via-lime-50 to-sky-50">
                                        <svg class="h-16 w-16 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 8h8M8 12h8M8 16h5" />
                                        </svg>
                                    </div>
                                @endif
                                @if ($article->category)
                                    <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-[11px] font-black uppercase text-emerald-700 shadow-sm">{{ $article->category }}</span>
                                @endif
                            </div>
                            <div class="p-5">
                                <h2 class="line-clamp-2 text-lg font-black leading-snug text-slate-950 group-hover:text-emerald-700">{{ $article->title }}</h2>
                                <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-500">{{ $article->excerpt }}</p>
                                <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4 text-xs font-semibold text-slate-400">
                                    <span class="truncate">{{ $article->author->name ?? 'Admin' }}</span>
                                    <span>{{ $article->published_at?->translatedFormat('d M Y') }}</span>
                                </div>
                                <span class="mt-4 inline-flex text-sm font-black text-emerald-700">Baca Selengkapnya</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $articles->links() }}
                </div>
            @endif
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs font-semibold text-slate-400">
        Agrilink - Sistem pertanian digital untuk distribusi, niaga, dan informasi harga.
    </footer>
</body>
</html>
