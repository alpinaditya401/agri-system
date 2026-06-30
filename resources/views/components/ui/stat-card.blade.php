@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'emerald',
])

@php
    $tones = [
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'red' => 'bg-red-50 text-red-700 ring-red-100',
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'ag-card p-5 transition hover:-translate-y-0.5 hover:shadow-md']) }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase text-slate-500">{{ $label }}</p>
            <p class="mt-3 text-3xl font-black text-slate-950">{{ $value }}</p>
            @if ($hint)
                <p class="mt-2 text-sm text-slate-500">{{ $hint }}</p>
            @endif
        </div>
        <div class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1 {{ $tones[$tone] ?? $tones['emerald'] }}">
            {{ $icon ?? '' }}
        </div>
    </div>
</div>
