<x-layouts.app :title="'Ajukan ' . $type->name . ' – Agrilink'">
    <x-slot:sidebar>
        @include('farmer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">Ajukan {{ $type->name }}</h1>
        <p class="text-gray-500 text-sm">Pilih distributor terdekat dan jumlah yang Anda perlukan.</p>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
            <form method="POST" action="{{ route('farmer.fertilizer.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="fertilizer_type_id" value="{{ $type->id }}">

                <div>
                    <label class="text-xs font-semibold text-gray-500">Pilih Distributor</label>
                    <select name="distributor_id" required class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                        <option value="">Pilih Distributor</option>
                        @forelse ($distributors as $dist)
                            <option value="{{ $dist->id }}">
                                {{ $dist->distributorProfile->company_name ?? $dist->name }}
                                @isset($dist->distance_km) ({{ number_format($dist->distance_km, 1) }} km) @endisset
                            </option>
                        @empty
                            <option value="" disabled>Tidak ada distributor dengan stok tersedia</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-500">Jumlah Diminta (kg)</label>
                    <input type="number" name="requested_kg" min="1" max="{{ $quota->remaining_kg ?? 0 }}" required
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                    <p class="text-[11px] text-gray-400 mt-1">Maksimal {{ number_format($quota->remaining_kg ?? 0) }} kg (sisa kuota Anda).</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Ajukan Sekarang</button>
                    <a href="{{ route('farmer.fertilizer.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Batal</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm h-fit">
            <h2 class="font-bold text-gray-800 mb-3">Info Kuota</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Total Alokasi</span><span class="font-semibold text-gray-800">{{ number_format($quota->allocated_kg ?? 0) }} kg</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Terpakai</span><span class="font-semibold text-gray-800">{{ number_format($quota->used_kg ?? 0) }} kg</span></div>
                <div class="flex justify-between pt-2 border-t border-gray-100"><span class="text-gray-700 font-semibold">Sisa Kuota</span><span class="font-bold text-emerald-700">{{ number_format($quota->remaining_kg ?? 0) }} kg</span></div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400">Harga subsidi</p>
                <p class="font-bold text-gray-800">Rp {{ number_format($type->subsidy_price_per_kg, 0, ',', '.') }}/kg</p>
            </div>
        </div>
    </div>
</x-layouts.app>
