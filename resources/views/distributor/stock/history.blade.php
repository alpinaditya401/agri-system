<x-layouts.app :title="'Riwayat Stok – Agrilink'">
    <x-slot:sidebar>
        @include('distributor._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">Riwayat Stok Pupuk</h1>
        <p class="text-gray-500 text-sm">Histori penerimaan stok pupuk di gudang Anda.</p>
    </x-slot:header>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">Jenis Pupuk</th>
                        <th class="p-4 font-semibold">Batch</th>
                        <th class="p-4 font-semibold">Jumlah</th>
                        <th class="p-4 font-semibold">Reservasi</th>
                        <th class="p-4 font-semibold">Tanggal Terima</th>
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
                            <td class="p-4 text-gray-500 text-xs">{{ $stock->received_date?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="p-4 text-gray-400 text-xs">{{ $stock->expiry_date?->translatedFormat('d M Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400 text-sm italic">Belum ada riwayat stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $stocks->links() }}
    </div>
</x-layouts.app>
