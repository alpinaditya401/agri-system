<x-layouts.app :title="'Kuota Pupuk – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Kuota Pupuk Bersubsidi
        </h1>
        <p class="text-gray-500 text-sm">Alokasikan dan pantau kuota pupuk per petani.</p>
    </x-slot:header>

    <div class="flex justify-end mb-5">
        <a href="{{ route('admin.fertilizer.quota.report') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Lihat Laporan Distribusi →</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold">Petani</th>
                            <th class="p-4 font-semibold">Pupuk</th>
                            <th class="p-4 font-semibold">Musim</th>
                            <th class="p-4 font-semibold">Alokasi</th>
                            <th class="p-4 font-semibold">Terpakai</th>
                            <th class="p-4 font-semibold">Sisa</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @forelse ($quotas as $quota)
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-4 font-medium text-gray-800">{{ $quota->farmer->name ?? '-' }}</td>
                                <td class="p-4 text-gray-600">{{ $quota->fertilizerType->name ?? '-' }}</td>
                                <td class="p-4 text-gray-500">{{ $quota->season }} {{ $quota->year }}</td>
                                <td class="p-4 text-gray-700">{{ number_format($quota->allocated_kg) }} kg</td>
                                <td class="p-4 text-gray-500">{{ number_format($quota->used_kg) }} kg</td>
                                <td class="p-4 font-semibold text-emerald-700">{{ number_format($quota->remaining_kg) }} kg</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400 text-sm italic">Belum ada kuota dialokasikan tahun ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $quotas->links() }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm h-fit">
            <h2 class="font-bold text-gray-800 mb-4">Alokasikan Kuota</h2>
            <form method="POST" action="{{ route('admin.fertilizer.quota.allocate') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-gray-500">Petani Terverifikasi</label>
                    <select name="farmer_id" required class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400" @disabled($farmers->isEmpty())>
                        <option value="">{{ $farmers->isEmpty() ? 'Belum ada petani eligible' : 'Pilih petani' }}</option>
                        @foreach ($farmers as $farmer)
                            <option value="{{ $farmer->id }}" @selected(old('farmer_id') == $farmer->id)>
                                {{ $farmer->name }} — {{ $farmer->email }}
                                @if ($farmer->farmerProfile?->nik)
                                    · NIK {{ $farmer->farmerProfile->nik }}
                                @endif
                                @if ($farmer->farmerProfile?->farmer_group_name)
                                    · {{ $farmer->farmerProfile->farmer_group_name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('farmer_id')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @else
                        <p class="text-[11px] text-gray-400 mt-1">Hanya petani aktif dan terverifikasi yang bisa menerima alokasi kuota.</p>
                    @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Jenis Pupuk</label>
                    <select name="fertilizer_type_id" required class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                        <option value="">Pilih</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected(old('fertilizer_type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('fertilizer_type_id')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Musim</label>
                        <select name="season" required class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                            <option value="MT1" @selected(old('season', 'MT1') === 'MT1')>MT1</option>
                            <option value="MT2" @selected(old('season') === 'MT2')>MT2</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Tahun</label>
                        <input type="number" name="year" value="{{ old('year', now()->year) }}" required
                               class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Jumlah Alokasi (kg)</label>
                    <input type="number" name="allocated_kg" min="1" value="{{ old('allocated_kg') }}" required
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                    @error('allocated_kg')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @if ($errors->has('allocate'))
                    <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">{{ $errors->first('allocate') }}</div>
                @endif
                <button type="submit" @disabled($farmers->isEmpty()) class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-gray-300 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">Alokasikan Kuota</button>
            </form>
        </div>
    </div>
</x-layouts.app>
