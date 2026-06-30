@props(['active' => 'home', 'tone' => 'dark'])

@php
    $links = [
        ['key' => 'home', 'label' => 'Beranda', 'url' => route('home')],
        ['key' => 'prices', 'label' => 'Harga Komoditas', 'url' => route('public.prices')],
        ['key' => 'products', 'label' => 'Produk', 'url' => route('products.index')],
        ['key' => 'map', 'label' => 'Peta', 'url' => route('public.map')],
        ['key' => 'articles', 'label' => 'Artikel', 'url' => route('public.articles')],
    ];
@endphp

<nav class="ag-public-nav">
    <div class="ag-container flex min-h-16 items-center justify-between gap-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Ke beranda Agrilink">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white shadow-sm">
                <img src="{{ asset('images/agrilink_logo.webp') }}" alt="Logo Agrilink" class="h-7 w-7 object-contain" onerror="this.style.display='none'">
            </span>
            <span class="text-lg font-black text-white">Agrilink</span>
        </a>

        <div class="hidden items-center gap-1 rounded-full border border-white/10 bg-white/10 p-1 backdrop-blur md:flex">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" class="ag-public-link {{ $active === $link['key'] ? 'bg-white text-emerald-950 hover:bg-white hover:text-emerald-950' : '' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <div class="hidden items-center gap-2 md:flex">
            <x-public-auth-actions tone="glass" :show-register="true" />
        </div>

        <details class="relative md:hidden">
            <summary class="flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-white">
                <span class="sr-only">Buka menu</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </summary>
            <div class="absolute right-0 mt-3 w-72 rounded-3xl border border-white/20 bg-emerald-950/95 p-3 shadow-2xl backdrop-blur-xl">
                <div class="grid gap-1">
                    @foreach ($links as $link)
                        <a href="{{ $link['url'] }}" class="rounded-2xl px-4 py-3 text-sm font-semibold {{ $active === $link['key'] ? 'bg-white text-emerald-950' : 'text-emerald-50 hover:bg-white/10' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
                <div class="mt-3 border-t border-white/10 pt-3">
                    <x-public-auth-actions tone="glass" :show-register="true" />
                </div>
            </div>
        </details>
    </div>
</nav>
