<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Distributor – Agrilink</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    <!-- MOBILE TOPBAR -->
    <div class="md:hidden fixed top-0 left-0 w-full h-16 bg-emerald-900 flex items-center justify-between px-4 z-50 shadow-md">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto">
            <span class="font-bold text-white text-lg">Agrilink</span>
        </div>
        <button id="mobileMenuBtn" class="text-white p-2">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>

    <!-- OVERLAY FOR MOBILE -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-emerald-900 flex flex-col flex-shrink-0 h-screen overflow-y-auto transform -translate-x-full md:relative md:translate-x-0 transition-all duration-300">
        
        <!-- Desktop Toggle Button -->
        <button id="desktopToggleBtn" class="hidden md:flex absolute top-5 right-4 bg-emerald-500 rounded-full w-8 h-8 items-center justify-center text-white cursor-pointer z-50 shadow-lg hover:bg-emerald-400 transition-colors">
            <svg class="w-5 h-5 transition-transform duration-300" id="desktopToggleIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>

        <!-- Logo -->
        <div class="flex items-center gap-2 px-4 py-5 border-b border-emerald-700/50">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto flex-shrink-0">
            <span class="sidebar-text opacity-100 font-bold text-white text-base whitespace-nowrap overflow-hidden transition-all duration-300">Agrilink</span>
            <button id="mobileCloseBtn" class="md:hidden ml-auto text-emerald-200 hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-x-hidden">
            <a href="{{ route('distributor.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-white bg-emerald-700 text-sm font-medium cursor-pointer" title="Dashboard">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Dashboard</span>
            </a>
            <a href="{{ route('distributor.stock.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100 hover:bg-emerald-700/50 text-sm font-medium cursor-pointer transition-all" title="Stok Pupuk">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Stok Pupuk</span>
            </a>
            <a href="{{ route('distributor.fertilizer.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-100 hover:bg-emerald-700/50 text-sm font-medium cursor-pointer transition-all" title="Permintaan Subsidi">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="sidebar-text opacity-100 whitespace-nowrap overflow-hidden transition-all duration-300">Permintaan Subsidi</span>
            </a>
        </nav>

        <!-- User Badge -->
        <div class="mt-auto border-t border-emerald-700/50 p-4">
            <div class="flex items-start gap-3">
                <a href="{{ route('profile.edit') }}" class="w-10 h-10 flex-shrink-0 bg-emerald-400 rounded-full flex items-center justify-center text-white font-bold text-lg hover:bg-emerald-300 transition-colors shadow-sm" title="Buka Profile">
                    {{ substr(auth()->user()->name ?? 'D', 0, 1) }}
                </a>
                <div class="sidebar-text opacity-100 whitespace-nowrap transition-all duration-300 overflow-hidden flex-1">
                    <a href="{{ route('profile.edit') }}" class="block hover:opacity-80 transition-opacity" title="Buka Profile">
                        <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->name ?? 'Distributor' }}</p>
                    </a>
                    <div class="flex items-center justify-between mt-1">
                        <a href="{{ route('profile.edit') }}" class="text-emerald-300 text-xs truncate hover:text-emerald-100 transition-colors" title="Buka Profile">Distributor</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-red-300 hover:text-red-100 text-xs font-medium flex items-center gap-1 transition-colors bg-red-900/30 hover:bg-red-900/50 px-2 py-1 rounded-md" title="Keluar">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 overflow-y-auto p-5 pt-20 md:pt-5 w-full">

        <!-- Welcome Header -->
        <div class="mb-5">
            <h1 class="text-lg font-bold text-gray-800">
                Selamat Datang, {{ auth()->user()->name }} (Distributor)
            </h1>
            <p class="text-gray-500 text-sm">Kelola stok pupuk bersubsidi dan pantau permintaan dari petani di wilayah Anda.</p>
        </div>

        <div class="flex flex-wrap gap-3 mb-5">
            <a href="{{ route('distributor.stock.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">Kelola Stok</a>
            <a href="{{ route('distributor.stock.history') }}" class="bg-white hover:bg-emerald-50 border border-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">Riwayat Stok</a>
            <a href="{{ route('distributor.fertilizer.index') }}" class="bg-white hover:bg-emerald-50 border border-gray-200 text-gray-700 font-semibold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">Proses Permintaan</a>
        </div>

        <!-- STAT CARDS ROW -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-emerald-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">TOTAL STOK GUDANG</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_stock_kg'] ?? 0, 0, ',', '.') }} Kg</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-yellow-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">STOK DI-RESERVASI</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_reserved_kg'] ?? 0, 0, ',', '.') }} Kg</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">PERMINTAAN PENDING</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['pending_requests'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-teal-500">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">PERMINTAAN DISETUJUI</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['approved_requests'] ?? 0 }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
            <div class="bg-white rounded-xl p-5 shadow-sm lg:col-span-2">
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

            <div class="bg-white rounded-xl p-5 shadow-sm">
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
        <div class="bg-white rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-800">Permintaan Subsidi Terbaru</h2>
                <a href="{{ route('distributor.fertilizer.index') }}" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
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

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const desktopToggleBtn = document.getElementById('desktopToggleBtn');
            const desktopToggleIcon = document.getElementById('desktopToggleIcon');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileCloseBtn = document.getElementById('mobileCloseBtn');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            let isCollapsed = false;

            // Desktop Toggle
            desktopToggleBtn.addEventListener('click', () => {
                isCollapsed = !isCollapsed;
                if (isCollapsed) {
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-20');
                    desktopToggleIcon.classList.add('rotate-180');
                    sidebarTexts.forEach(el => {
                        el.classList.remove('opacity-100');
                        el.classList.add('opacity-0', 'w-0');
                    });
                } else {
                    sidebar.classList.remove('w-20');
                    sidebar.classList.add('w-64');
                    desktopToggleIcon.classList.remove('rotate-180');
                    sidebarTexts.forEach(el => {
                        el.classList.remove('opacity-0', 'w-0');
                        el.classList.add('opacity-100');
                    });
                }
            });

            // Mobile Toggle
            function toggleMobileSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            }

            mobileMenuBtn.addEventListener('click', toggleMobileSidebar);
            mobileCloseBtn.addEventListener('click', toggleMobileSidebar);
            sidebarOverlay.addEventListener('click', toggleMobileSidebar);
        });

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
</body>
</html>
