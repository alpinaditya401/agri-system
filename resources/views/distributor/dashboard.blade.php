<x-layouts.app :title="'Dashboard Distributor – Agrilink'">
    <x-slot:sidebar>
        @include('distributor._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading">Selamat Datang, {{ auth()->user()->name }} (Distributor)</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola stok pupuk bersubsidi dan pantau permintaan dari petani di wilayah Anda.</p>
    </x-slot:header>

    <x-dashboard-region-filter :filters="$regionFilters ?? []" :options="$regionOptions ?? []" description="Filter permintaan subsidi berdasarkan wilayah petani pemohon." />

    <div class="mb-5 flex flex-wrap gap-3">
        <a href="{{ route('distributor.stock.index') }}" class="ag-btn-primary">Kelola Stok</a>
        <a href="{{ route('distributor.stock.history') }}" class="ag-btn-secondary">Riwayat Stok</a>
        <a href="{{ route('distributor.fertilizer.index') }}" class="ag-btn-secondary">Proses Permintaan</a>
        <a href="{{ route('notifications.index', ['filter' => 'stok']) }}" class="ag-btn-secondary">Alert Stok</a>
    </div>

    <!-- STAT CARDS ROW -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="ag-card p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">TOTAL STOK GUDANG</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_stock_kg'] ?? 0, 0, ',', '.') }} Kg</p>
        </div>
        <div class="ag-card p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">STOK DI-RESERVASI</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_reserved_kg'] ?? 0, 0, ',', '.') }} Kg</p>
        </div>
        <div class="ag-card p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">PERMINTAAN PENDING</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['pending_requests'] ?? 0 }}</p>
        </div>
        <div class="ag-card p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">PERMINTAAN DISETUJUI</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['approved_requests'] ?? 0 }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
        <div class="ag-card p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-800">Rincian Stok Pupuk</h2>
                <a href="{{ route('distributor.stock.index') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Update stok</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @forelse($stockBreakdown as $stock)
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">{{ $stock->fertilizerType->name ?? 'Pupuk' }}</p>
                            <p class="text-xs text-gray-500">Batch {{ $stock->batch_number ?? '-' }} • Masuk {{ optional($stock->received_date)->format('d M Y') ?? '-' }}</p>
                        </div>
                        <span class="text-xs bg-emerald-100 text-emerald-700 font-semibold px-2.5 py-1 rounded-full">{{ number_format($stock->stock_kg, 0, ',', '.') }} Kg</span>
                    </div>
                    <div class="mt-3 h-2 bg-gray-200 rounded-full overflow-hidden">
                        @php
                            $available = max((float) $stock->stock_kg - (float) $stock->reserved_kg, 0);
                            $availablePercent = $stock->stock_kg > 0 ? min(100, round(($available / $stock->stock_kg) * 100)) : 0;
                        @endphp
                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $availablePercent }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-2">
                        <span>Tersedia {{ number_format($available, 0, ',', '.') }} Kg</span>
                        <span>Reservasi {{ number_format($stock->reserved_kg, 0, ',', '.') }} Kg</span>
                    </div>
                </div>
                @empty
                <div class="md:col-span-2 bg-gray-50 rounded-xl p-5 text-center">
                    <p class="font-semibold text-gray-700 text-sm">Stok belum tercatat</p>
                    <p class="text-xs text-gray-500 mt-1">Tambahkan stok pupuk agar permintaan petani bisa diproses.</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="ag-card p-5">
            <h2 class="font-bold text-gray-800 mb-4">Status Permintaan</h2>
            <canvas id="requestChart" height="190"></canvas>
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex items-center justify-between bg-yellow-50 rounded-lg px-3 py-2">
                    <span class="text-yellow-700 font-medium">Pending</span>
                    <span class="font-bold text-yellow-700">{{ $stats['pending_requests'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between bg-blue-50 rounded-lg px-3 py-2">
                    <span class="text-blue-700 font-medium">Disetujui</span>
                    <span class="font-bold text-blue-700">{{ $stats['approved_requests'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between bg-emerald-50 rounded-lg px-3 py-2">
                    <span class="text-emerald-700 font-medium">Diserahkan</span>
                    <span class="font-bold text-emerald-700">{{ $stats['dispensed_total'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TRANSACTIONS TABLE -->
    <div class="ag-card p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-gray-800">Permintaan Subsidi Terbaru</h2>
            <a href="{{ route('distributor.fertilizer.index') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Lihat Semua</a>
        </div>
        <div class="ag-table-wrap">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-3 rounded-tl-lg rounded-bl-lg font-semibold">No. Transaksi</th>
                        <th class="p-3 font-semibold">Petani</th>
                        <th class="p-3 font-semibold">Pupuk</th>
                        <th class="p-3 font-semibold">Jumlah (Kg)</th>
                        <th class="p-3 font-semibold">Status</th>
                        <th class="p-3 rounded-tr-lg rounded-br-lg font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse($recentTransactions as $transaction)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="p-3 font-medium text-emerald-700">#{{ $transaction->transaction_number }}</td>
                        <td class="p-3 text-gray-800">{{ $transaction->farmer->name ?? 'Unknown' }}</td>
                        <td class="p-3 text-gray-600">{{ $transaction->fertilizerType->name ?? '-' }}</td>
                        <td class="p-3 text-gray-800 font-medium">{{ $transaction->requested_kg }} Kg</td>
                        <td class="p-3">
                            @if($transaction->status == 'pending')
                                <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-1 rounded-md">Pending</span>
                            @elseif($transaction->status == 'approved')
                                <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-md">Disetujui</span>
                            @elseif($transaction->status == 'dispensed')
                                <span class="bg-emerald-100 text-emerald-700 text-xs font-semibold px-2.5 py-1 rounded-md">Selesai</span>
                            @else
                                <span class="bg-red-100 text-red-700 text-xs font-semibold px-2.5 py-1 rounded-md">{{ ucfirst($transaction->status) }}</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <a href="{{ route('distributor.fertilizer.show', $transaction) }}" class="text-emerald-600 hover:text-emerald-800 text-sm font-medium">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-5 text-center text-gray-500 text-sm italic">Belum ada permintaan transaksi baru.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        new Chart(document.getElementById('requestChart'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Disetujui', 'Diserahkan'],
                datasets: [{
                    data: [
                        {{ $stats['pending_requests'] ?? 0 }},
                        {{ $stats['approved_requests'] ?? 0 }},
                        {{ $stats['dispensed_total'] ?? 0 }}
                    ],
                    backgroundColor: ['#facc15', '#3b82f6', '#10b981'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                cutout: '65%',
                plugins: { legend: { display: false } }
            }
        });
    </script>
    @endpush
</x-layouts.app>

