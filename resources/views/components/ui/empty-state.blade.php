@props([
    'title' => 'Data belum tersedia',
    'message' => 'Belum ada data yang bisa ditampilkan saat ini.',
])

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm']) }}>
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 12.5c5.2-6.8 12-7.4 15.5-4.4-2.1 5.9-9 8.8-15.5 4.4Z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.7 12.5c5.1.2 9.7-1.4 13.4-4.3" />
        </svg>
    </div>
    <h2 class="mt-4 text-lg font-bold text-slate-900">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ $message }}</p>
    @if (trim($slot))
        <div class="mt-5">
            {{ $slot }}
        </div>
    @endif
</div>
