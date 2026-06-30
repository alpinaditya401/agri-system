<x-layouts.app :title="'Laporan Kuota Pupuk – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">Laporan Pergerakan Stok Pupuk</h1>
        <p class="text-gray-500 text-sm">Rekap distribusi pupuk bersubsidi tahun {{ $year }}.</p>
    </x-slot:header>

    <form method="GET" class="flex gap-2 mb-5">
        <select name="year" onchange="this.form.submit()" class="px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
            @for ($y = now()->year; $y >= now()->year - 3; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">Distributor</th>
                        <th class="p-4 font-semibold">Jenis Pupuk</th>
                        <th class="p-4 font-semibold">Bulan</th>
                        <th class="p-4 font-semibold">Jumlah Transaksi</th>
                        <th class="p-4 font-semibold">Total Disalurkan</th>
                        <th class="p-4 font-semibold">Total Nilai</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($report as $row)
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 font-medium text-gray-800">{{ $row->distributor_name }}</td>
                            <td class="p-4 text-gray-600">{{ $row->fertilizer_name }}</td>
                            <td class="p-4 text-gray-500">{{ \Carbon\Carbon::createFromDate($year, $row->month, 1)->translatedFormat('F') }}</td>
                            <td class="p-4 text-gray-700">{{ $row->transaction_count }}</td>
                            <td class="p-4 text-gray-700">{{ number_format($row->total_kg_dispensed) }} kg</td>
                            <td class="p-4 font-semibold text-emerald-700">Rp {{ number_format($row->total_value, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400 text-sm italic">Belum ada data distribusi untuk tahun ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
