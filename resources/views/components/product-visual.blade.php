@props([
    'product' => null,
    'iconClass' => 'h-12 w-12',
    'frameClass' => 'h-16 w-16 rounded-2xl',
    'imageClass' => 'h-full w-full object-cover transition-transform duration-300 group-hover:scale-105',
])

@php
    $productName = \Illuminate\Support\Str::lower((string) data_get($product, 'name', ''));
    $categoryName = \Illuminate\Support\Str::lower((string) data_get($product, 'category.name', ''));
    $image = data_get($product, 'main_image_url') ?: data_get($product, 'main_image');

    $variant = 'default';

    if (\Illuminate\Support\Str::contains($productName, ['beras'])) {
        $variant = 'grain';
    } elseif (\Illuminate\Support\Str::contains($productName, ['gabah', 'padi', 'rice'])) {
        $variant = 'rice';
    } elseif (\Illuminate\Support\Str::contains($productName, ['bawang', 'onion'])) {
        $variant = 'onion';
    } elseif (\Illuminate\Support\Str::contains($productName, ['cabai', 'cabe', 'chili', 'rawit'])) {
        $variant = 'chili';
    } elseif (\Illuminate\Support\Str::contains($productName, ['kangkung', 'bayam', 'sawi', 'sayur', 'hortikultura', 'buah', 'tomat', 'wortel'])) {
        $variant = 'vegetable';
    } elseif (\Illuminate\Support\Str::contains($productName, ['ayam'])) {
        $variant = 'poultry';
    } elseif (\Illuminate\Support\Str::contains($productName, ['jagung', 'corn'])) {
        $variant = 'corn';
    } elseif (\Illuminate\Support\Str::contains($productName, ['kentang', 'umbi', 'singkong', 'ubi'])) {
        $variant = 'tuber';
    } elseif (\Illuminate\Support\Str::contains($productName, ['kedelai', 'kacang'])) {
        $variant = 'legume';
    } elseif (\Illuminate\Support\Str::contains($categoryName, ['beras'])) {
        $variant = 'grain';
    } elseif (\Illuminate\Support\Str::contains($categoryName, ['padi'])) {
        $variant = 'rice';
    } elseif (\Illuminate\Support\Str::contains($categoryName, ['hortikultura', 'sayur', 'buah'])) {
        $variant = 'vegetable';
    }

    $themes = [
        'rice' => 'from-amber-50 via-yellow-50 to-lime-100 text-amber-700',
        'grain' => 'from-slate-50 via-white to-cyan-100 text-slate-700',
        'onion' => 'from-rose-50 via-pink-50 to-fuchsia-100 text-rose-700',
        'chili' => 'from-red-50 via-orange-50 to-amber-100 text-red-700',
        'vegetable' => 'from-emerald-50 via-teal-50 to-lime-100 text-emerald-700',
        'corn' => 'from-yellow-50 via-amber-50 to-green-100 text-yellow-700',
        'tuber' => 'from-orange-50 via-amber-50 to-stone-100 text-orange-700',
        'legume' => 'from-lime-50 via-green-50 to-emerald-100 text-lime-700',
        'poultry' => 'from-orange-50 via-amber-50 to-yellow-100 text-orange-700',
        'default' => 'from-sky-50 via-emerald-50 to-teal-100 text-teal-700',
    ];

    $theme = $themes[$variant] ?? $themes['default'];
    $commodityImages = [
        'rice' => 'images/commodities/gabah.webp',
        'grain' => 'images/commodities/beras.webp',
        'onion' => 'images/commodities/bawang-merah.webp',
        'chili' => 'images/commodities/cabai-merah.webp',
        'vegetable' => 'images/commodities/kangkung.webp',
        'poultry' => 'images/commodities/ayam.webp',
    ];

    if (! $image && isset($commodityImages[$variant]) && file_exists(public_path($commodityImages[$variant]))) {
        $image = asset($commodityImages[$variant]);
    }
@endphp

<div {{ $attributes->merge(['class' => 'relative flex items-center justify-center overflow-hidden bg-gradient-to-br ' . $theme]) }}>
    @if ($image)
        <img
            src="{{ $image }}"
            alt="{{ data_get($product, 'name', 'Produk pertanian') }}"
            loading="lazy"
            class="{{ $imageClass }}"
            onerror="this.classList.add('hidden'); const fallback = this.parentElement.querySelector('[data-product-fallback]'); if (fallback) { fallback.classList.remove('hidden'); fallback.classList.add('flex'); }"
        >
    @endif

    <div data-product-fallback class="{{ $image ? 'hidden' : 'flex' }} absolute inset-0 items-center justify-center">
        <div class="absolute inset-0 opacity-45 [background-image:linear-gradient(135deg,rgba(255,255,255,.65)_25%,transparent_25%,transparent_50%,rgba(255,255,255,.65)_50%,rgba(255,255,255,.65)_75%,transparent_75%,transparent)] [background-size:18px_18px]"></div>
        <div class="relative flex {{ $frameClass }} items-center justify-center bg-white/70 shadow-sm ring-1 ring-white/80">
            @switch($variant)
                @case('rice')
                    <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 20V8m0 0c2.8-3.2 5.5-3.6 8-1.3-1.2 3-4.7 4.2-8 1.3Zm0 3.5c-3-2.7-5.8-3-8-1 1.2 3.1 4.7 4.5 8 1ZM8 20h8" />
                    </svg>
                    @break
                @case('grain')
                    <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.2 9.5h9.6l-.8 8.8a2 2 0 0 1-2 1.8h-4a2 2 0 0 1-2-1.8l-.8-8.8Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 9.5c0-2 1.2-3.4 3-3.4s3 1.4 3 3.4M10 4.2h4" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M10 13h.1M12.1 14.6h.1M14.2 13h.1M10.9 17h.1M13.3 16.8h.1" />
                    </svg>
                    @break
                @case('onion')
                    <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.2c-1.8 1.1-5.2 3.5-5.2 7.3 0 3.2 2.3 5.7 5.2 5.7s5.2-2.5 5.2-5.7c0-3.8-3.4-6.2-5.2-7.3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.2c-.3-1.7.3-3 1.7-3.8M12 6.2c.8-1.6 2.1-2.4 4-2.3M12 6.2c-.9-1.4-2.2-2.1-4-2" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M10.3 10.5c-.8 1.2-1 2.6-.5 4.1M13.7 10.5c.8 1.2 1 2.6.5 4.1" />
                    </svg>
                    @break
                @case('chili')
                    <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.2 8.2c4.7-2.4 8.8.1 8.4 4.3-.4 3.7-3.7 6.1-8 6.4 2.1-1.7 3.1-3.3 3-4.9-.1-2-1.3-3.6-3.4-5.8Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.2 8.2c-.1-2 1.3-3.4 3.5-3.5m0 0c.1 1.3-.5 2.4-1.8 3.2" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M12.5 10.3c1.2.8 1.8 1.9 1.7 3.3" />
                    </svg>
                    @break
                @case('vegetable')
                    <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.8 12.8C9.7 6.3 16.2 5.7 19.5 8.6c-1.9 5.6-8.6 8.2-14.7 4.2Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12.8c4.8.2 9.1-1.3 12.6-4.1M8.2 16.4c-1.4 1.2-2.2 2.6-2.5 4.1" />
                    </svg>
                    @break
                @case('corn')
                    <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.2c2.1 0 3.5 2.7 3.5 6.3S14.1 17 12 17s-3.5-2.9-3.5-6.5 1.4-6.3 3.5-6.3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.7 12.1c-2.1.7-3.7 2.5-4.2 5.4 3.1-.2 5.1-1.5 6-3.8M15.3 12.1c2.1.7 3.7 2.5 4.2 5.4-3.1-.2-5.1-1.5-6-3.8" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M10.5 7.2h3M10.2 10h3.6M10.5 12.8h3" />
                    </svg>
                    @break
                @case('tuber')
                    <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7c2.7 0 4.8 2.4 4.8 5.8 0 3.5-2.2 6.3-4.8 6.3s-4.8-2.8-4.8-6.3C7.2 9.4 9.3 7 12 7Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7c-.9-1.5-2.2-2.4-4.1-2.8M12 7c1.1-1.5 2.6-2.3 4.5-2.2" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M10 11.2h.1M14 13.8h.1M11.5 16.1h.1" />
                    </svg>
                    @break
                @case('legume')
                    <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.3 7.2c4.5-2.3 8.4.9 8 5.2-.4 4.1-4.2 6.7-8.9 5.8 2.4-1.3 3.4-3.1 2.9-5.2-.4-1.7-1.2-3.3-2-5.8Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M10.7 9.1h.1M12.6 12.2h.1M11.1 15.4h.1" />
                    </svg>
                    @break
                @default
                    <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 9.2h14l-1.2 9H6.2l-1.2-9Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 9.2c.7-2.8 2.1-4.2 4-4.2s3.3 1.4 4 4.2M11.6 5.2C10.8 3.8 9.5 3.1 8 3.1m4.4 2.1c.9-1.4 2.1-2.1 3.7-2.1" />
                    </svg>
            @endswitch
        </div>
    </div>
</div>
