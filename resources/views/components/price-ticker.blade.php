@props(['items' => []])

<div class="bg-gradient-to-r from-emerald-700 to-emerald-500 rounded-2xl p-4 mb-6 overflow-hidden">
    <div class="flex items-center gap-3">
        <span class="bg-amber-300 text-emerald-900 text-[9px] font-black px-2 py-0.5 rounded-full flex-shrink-0">LIVE</span>
        <div class="overflow-hidden flex-1">
            <div class="flex gap-8 whitespace-nowrap" style="animation: agrilinkTicker 22s linear infinite;">
                @foreach ($items as $item)
                    <span class="text-xs text-white/90">
                        <strong class="text-white">{{ $item['name'] }}</strong>
                        Rp {{ number_format($item['price'], 0, ',', '.') }}/{{ $item['unit'] ?? 'kg' }}
                        <span class="{{ ($item['up'] ?? true) ? 'text-emerald-100' : 'text-red-200' }}">
                            {{ ($item['up'] ?? true) ? '▲' : '▼' }} {{ $item['change'] ?? 0 }}%
                        </span>
                    </span>
                @endforeach
                @foreach ($items as $item)
                    <span class="text-xs text-white/90">
                        <strong class="text-white">{{ $item['name'] }}</strong>
                        Rp {{ number_format($item['price'], 0, ',', '.') }}/{{ $item['unit'] ?? 'kg' }}
                        <span class="{{ ($item['up'] ?? true) ? 'text-emerald-100' : 'text-red-200' }}">
                            {{ ($item['up'] ?? true) ? '▲' : '▼' }} {{ $item['change'] ?? 0 }}%
                        </span>
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
<style>@keyframes agrilinkTicker { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }</style>
