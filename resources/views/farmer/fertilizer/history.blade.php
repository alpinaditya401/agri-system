<x-layouts.app :title="'Riwayat Pupuk Subsidi – Agrilink'">
    <x-slot:sidebar>
        @include('farmer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">Riwayat Pengajuan Pupuk</h1>
        <p class="text-gray-500 text-sm">Status seluruh pengajuan pupuk bersubsidi Anda.</p>
    </x-slot:header>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">No. Transaksi</th>
                        <th class="p-4 font-semibold">Jenis Pupuk</th>
                        <th class="p-4 font-semibold">Distributor</th>
                        <th class="p-4 font-semibold">Jumlah</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Tanggal</th>
                        <th class="p-4 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($transactions as $tx)
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 font-medium text-emerald-700">{{ $tx->transaction_number }}</td>
                            <td class="p-4 text-gray-800">{{ $tx->fertilizerType->name ?? '-' }}</td>
                            <td class="p-4 text-gray-600">{{ $tx->distributor->name ?? '-' }}</td>
                            <td class="p-4 text-gray-700">{{ $tx->requested_kg }} kg</td>
                            <td class="p-4">
                                @php
                                    $badge = match($tx->status) {
                                        'pending' => 'bg-yellow-50 text-yellow-700',
                                        'approved' => 'bg-blue-50 text-blue-700',
                                        'dispensed' => 'bg-emerald-50 text-emerald-700',
                                        'rejected', 'cancelled' => 'bg-red-50 text-red-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-md {{ $badge }}">{{ ucfirst($tx->status) }}</span>
                            </td>
                            <td class="p-4 text-gray-400 text-xs">{{ $tx->created_at->translatedFormat('d M Y') }}</td>
                            <td class="p-4"><a href="{{ route('farmer.fertilizer.transactions.show', $tx) }}" class="text-emerald-600 hover:text-emerald-800 font-medium">Detail</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400 text-sm italic">Belum ada riwayat pengajuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $transactions->links() }}
    </div>
</x-layouts.app>
