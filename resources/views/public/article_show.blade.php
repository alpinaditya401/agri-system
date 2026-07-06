<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->title }} - Agrilink</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
    <x-public-nav active="articles" />

    <main class="pt-20">
        <article class="ag-container max-w-5xl py-10">
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

            <nav class="mb-6 flex items-center gap-2 text-sm font-semibold text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-emerald-700">Beranda</a>
                <span>/</span>
                <a href="{{ route('public.articles') }}" class="hover:text-emerald-700">Artikel</a>
                <span>/</span>
                <span class="truncate text-slate-900">{{ $article->title }}</span>
            </nav>

            <header class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm md:p-10">
                @if ($article->category)
                    <x-ui.badge tone="success">{{ $article->category }}</x-ui.badge>
                @endif
                <h1 class="mt-5 text-4xl font-black leading-tight text-slate-950 md:text-6xl">{{ $article->title }}</h1>
                <div class="mt-5 flex flex-wrap items-center gap-3 text-sm font-semibold text-slate-500">
                    <span>{{ $article->author->name ?? 'Admin' }}</span>
                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                    <span>{{ $article->published_at?->translatedFormat('d F Y') }}</span>
                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                    <span>{{ number_format($article->view_count, 0, ',', '.') }} kali dilihat</span>
                </div>
            </header>

            @if ($coverImage && file_exists(public_path($coverImage)))
                <div class="mt-6 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <img src="{{ asset($coverImage) }}" alt="{{ $article->title }}" class="max-h-[520px] w-full object-cover">
                </div>
            @endif

            <div class="mx-auto mt-8 max-w-3xl rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm md:p-10">
                <div class="space-y-5 text-base leading-8 text-slate-700">
                    {!! nl2br(e($article->content)) !!}
                </div>
            </div>

            @if (collect($article->tags ?? [])->contains('bps'))
                <section class="mx-auto mt-8 max-w-3xl rounded-[2rem] border border-emerald-200 bg-white p-6 shadow-sm md:p-8">
                    <p class="ag-label">Sumber Data</p>
                    <h2 class="mt-3 text-2xl font-black text-slate-950">Link Tabel BPS Utuh</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Semua link mengarah ke halaman web resmi BPS, bukan endpoint API terpisah.</p>
                    <div class="mt-5 grid gap-3">
                        @foreach (config('bps_sources.tables', []) as $table)
                            <a href="{{ $table['url'] }}" target="_blank" rel="noopener" class="rounded-2xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50">
                                <span class="block text-sm font-black text-emerald-700">{{ $table['label'] }}</span>
                                <span class="mt-1 block text-xs leading-6 text-slate-500">{{ $table['description'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($related->isNotEmpty())
                <section class="mt-12 border-t border-slate-200 pt-8">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="ag-label">Lanjut Baca</p>
                            <h2 class="mt-2 text-2xl font-black text-slate-950">Artikel Terkait</h2>
                        </div>
                        <a href="{{ route('public.articles') }}" class="ag-btn-secondary px-4 py-2">Semua artikel</a>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        @foreach ($related as $r)
                            <a href="{{ route('public.articles.show', $r->slug) }}" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-md">
                                <p class="line-clamp-3 font-black leading-snug text-slate-950">{{ $r->title }}</p>
                                <p class="mt-3 text-xs font-semibold text-slate-400">{{ $r->published_at?->translatedFormat('d M Y') }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </article>
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs font-semibold text-slate-400">
        Agrilink - Sistem pertanian digital untuk distribusi, niaga, dan informasi harga.
    </footer>
</body>
</html>
