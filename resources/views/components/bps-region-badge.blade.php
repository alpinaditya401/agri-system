@props(['region' => null])

<div class="flex items-start gap-3 bg-gradient-to-r from-emerald-900 to-emerald-700 rounded-2xl px-5 py-4 mb-5 flex-wrap">
    <svg class="w-5 h-5 text-amber-300 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="flex-1 min-w-0">
        <p class="text-[10px] text-emerald-200 font-bold uppercase tracking-wide">Sumber Data - Badan Pusat Statistik (BPS)</p>
        <p class="text-amber-200 font-extrabold text-base mt-0.5">{{ $region ?? 'Nasional' }}</p>
        <p class="text-[11px] text-emerald-200 mt-0.5">www.bps.go.id/id/statistics-table · diperbarui otomatis</p>
    </div>
</div>
