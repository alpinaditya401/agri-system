@props([
    'type' => 'info',
    'title' => null,
])

@php
    $styles = [
        'success' => [
            'wrap' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
            'icon' => 'bg-emerald-100 text-emerald-700',
            'title' => $title ?? 'Berhasil',
        ],
        'warning' => [
            'wrap' => 'border-amber-200 bg-amber-50 text-amber-800',
            'icon' => 'bg-amber-100 text-amber-700',
            'title' => $title ?? 'Perhatian',
        ],
        'danger' => [
            'wrap' => 'border-red-200 bg-red-50 text-red-800',
            'icon' => 'bg-red-100 text-red-700',
            'title' => $title ?? 'Gagal',
        ],
        'info' => [
            'wrap' => 'border-sky-200 bg-sky-50 text-sky-800',
            'icon' => 'bg-sky-100 text-sky-700',
            'title' => $title ?? 'Informasi',
        ],
    ][$type] ?? [
        'wrap' => 'border-slate-200 bg-slate-50 text-slate-800',
        'icon' => 'bg-slate-100 text-slate-700',
        'title' => $title ?? 'Informasi',
    ];
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl border px-4 py-3 text-sm font-semibold shadow-sm {$styles['wrap']}"]) }} role="{{ $type === 'danger' ? 'alert' : 'status' }}">
    <div class="flex gap-3">
        <span class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full {{ $styles['icon'] }}">
            @if ($type === 'success')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m5 13 4 4L19 7" /></svg>
            @elseif ($type === 'danger')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18 18 6M6 6l12 12" /></svg>
            @elseif ($type === 'warning')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v5m0 4h.01M10.3 4.2 2.7 17.4A2 2 0 0 0 4.4 20h15.2a2 2 0 0 0 1.7-2.6L13.7 4.2a2 2 0 0 0-3.4 0Z" /></svg>
            @else
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 17h.01M12 7v6m9-1a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            @endif
        </span>
        <div class="min-w-0 leading-6">
            <p class="font-black">{{ $styles['title'] }}</p>
            <div class="mt-1">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
