<x-layouts.app :title="'Pupuk Bersubsidi – Agrilink'">
    <x-slot:sidebar>
        @include('farmer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Pupuk Bersubsidi
        </h1>
        <p class="text-gray-500 text-sm">Ajukan permintaan pupuk sesuai kuota musim tanam Anda.</p>
    </x-slot:header>

    <div class="flex justify-end mb-5">
        <a href="{{ route('farmer.fertilizer.history') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Lihat Riwayat Pengajuan →</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($types as $type)
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="font-bold text-gray-800">{{ $type->name }}</h2>
                    <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-full">{{ $type->code }}</span>
                </div>
                <p class="text-xs text-gray-400 mb-4">{{ $type->description ?: 'Pupuk bersubsidi pemerintah.' }}</p>

                <div class="bg-gray-50 rounded-xl p-3 mb-4">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-500">Sisa Kuota</span>
                        <span class="font-bold text-emerald-700">{{ number_format($type->remaining_kg) }} kg</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $type->allocated_kg > 0 ? min(100, round($type->remaining_kg / $type->allocated_kg * 100)) : 0 }}%"></div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">dari total {{ number_format($type->allocated_kg) }} kg</p>
                </div>

                <p class="text-sm text-gray-600 mb-4">Harga subsidi: <span class="font-bold text-gray-800">Rp {{ number_format($type->subsidy_price_per_kg, 0, ',', '.') }}/kg</span></p>

                @if ($type->remaining_kg > 0)
                    <a href="{{ route('farmer.fertilizer.create', $type) }}" class="block text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                        Ajukan Permintaan
                    </a>
                @else
                    <button disabled class="w-full bg-gray-100 text-gray-400 font-semibold py-2.5 rounded-xl text-sm cursor-not-allowed">
                        Kuota Habis / Tidak Tersedia
                    </button>
                @endif
            </div>
        @empty
            <div class="md:col-span-2 lg:col-span-3 bg-white rounded-2xl border border-gray-100 p-10 text-center">
                <p class="text-gray-400 text-sm">Belum ada jenis pupuk bersubsidi yang aktif.</p>
            </div>
        @endforelse
    </div>
</x-layouts.app>
