<x-layouts.app :title="'Stok Pupuk – Agrilink'">
    <x-slot:sidebar>
        @include('distributor._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Stok Pupuk
        </h1>
        <p class="text-gray-500 text-sm">Kelola inventaris pupuk bersubsidi di gudang Anda.</p>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold">Jenis Pupuk</th>
                            <th class="p-4 font-semibold">Batch</th>
                            <th class="p-4 font-semibold">Stok</th>
                            <th class="p-4 font-semibold">Reservasi</th>
                            <th class="p-4 font-semibold">Kadaluarsa</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @forelse ($stocks as $stock)
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-4 font-medium text-gray-800">{{ $stock->fertilizerType->name ?? '-' }}</td>
                                <td class="p-4 text-gray-500">{{ $stock->batch_number ?? '-' }}</td>
                                <td class="p-4 text-gray-700 font-semibold">{{ number_format($stock->stock_kg, 0, ',', '.') }} kg</td>
                                <td class="p-4 text-gray-500">{{ number_format($stock->reserved_kg, 0, ',', '.') }} kg</td>
                                <td class="p-4 text-gray-400 text-xs">{{ $stock->expiry_date?->translatedFormat('d M Y') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-400 text-sm italic">Belum ada data stok. Tambahkan stok di formulir samping.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $stocks->links() }}
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm h-fit">
            <h2 class="font-bold text-gray-800 mb-4">+ Tambah Stok</h2>
            <form method="POST" action="{{ route('distributor.stock.add') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-gray-500">Jenis Pupuk</label>
                    <select name="fertilizer_type_id" required class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                        <option value="">Pilih</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Jumlah (kg)</label>
                    <input type="number" name="stock_kg" min="1" required class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">No. Batch (opsional)</label>
                    <input type="text" name="batch_number" class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Tanggal Terima</label>
                    <input type="date" name="received_date" value="{{ date('Y-m-d') }}" required class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Tanggal Kadaluarsa (opsional)</label>
                    <input type="date" name="expiry_date" class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">Simpan Stok</button>
            </form>
            <a href="{{ route('distributor.stock.history') }}" class="block text-center text-xs text-emerald-600 font-semibold hover:text-emerald-700 mt-3">Lihat Riwayat Stok →</a>
        </div>
    </div>
</x-layouts.app>
