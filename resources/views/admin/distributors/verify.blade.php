<x-layouts.app :title="'Verifikasi Distributor - Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading">Verifikasi Distributor</h1>
        <p class="mt-1 text-sm text-slate-500">Tinjau pengajuan user biasa yang ingin menjadi distributor pupuk subsidi.</p>
    </x-slot:header>

    <div class="mb-5 flex flex-wrap gap-2" id="verifyTabs">
        <button onclick="showTab('pending', this)" class="vt-btn active">Pending ({{ $pendingDistributors->total() }})</button>
        <button onclick="showTab('verified', this)" class="vt-btn">Terverifikasi ({{ $verifiedDistributors->total() }})</button>
        <button onclick="showTab('rejected', this)" class="vt-btn">Ditolak ({{ $rejectedDistributors->total() }})</button>
    </div>

    <div id="tab-pending" class="space-y-3">
        @forelse ($pendingDistributors as $distributor)
            <div class="ag-card p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-lg font-black text-amber-700">{{ substr($distributor->name, 0, 1) }}</div>
                        <div class="min-w-0">
                            <p class="font-black text-slate-900">{{ $distributor->name }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-600">{{ $distributor->distributorProfile->company_name ?? 'Usaha belum diisi' }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                Izin: {{ $distributor->distributorProfile->license_number ?? '-' }} ·
                                Kapasitas: {{ number_format($distributor->distributorProfile->storage_capacity_kg ?? 0, 0, ',', '.') }} kg ·
                                {{ $distributor->district ?? '-' }}, {{ $distributor->province ?? '-' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('admin.distributors.verify.approve', $distributor) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="ag-btn-primary px-4 py-2 text-xs">Setujui</button>
                        </form>
                        <button type="button" onclick="document.getElementById('reject-form-{{ $distributor->id }}').classList.toggle('hidden')" class="rounded-2xl bg-red-50 px-4 py-2 text-xs font-black text-red-600 transition hover:bg-red-100">Tolak</button>
                    </div>
                </div>
                <form id="reject-form-{{ $distributor->id }}" method="POST" action="{{ route('admin.distributors.verify.reject', $distributor) }}" class="mt-4 hidden gap-2 sm:flex">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="rejection_reason" required placeholder="Alasan penolakan..." class="ag-input">
                    <button type="submit" class="rounded-2xl bg-red-600 px-4 py-2 text-xs font-black text-white transition hover:bg-red-700">Kirim</button>
                </form>
            </div>
        @empty
            <x-ui.empty-state title="Tidak ada pengajuan distributor" message="Semua pengajuan distributor sudah diproses." />
        @endforelse
        <div class="mt-3">{{ $pendingDistributors->links() }}</div>
    </div>

    <div id="tab-verified" class="hidden space-y-3">
        @forelse ($verifiedDistributors as $distributor)
            <div class="ag-card flex items-center gap-3 p-5">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 font-black text-emerald-700">{{ substr($distributor->name, 0, 1) }}</div>
                <div>
                    <p class="font-black text-slate-900">{{ $distributor->name }}</p>
                    <p class="text-xs font-semibold text-slate-500">{{ $distributor->distributorProfile->company_name ?? '-' }} · {{ $distributor->district ?? '-' }}</p>
                </div>
            </div>
        @empty
            <x-ui.empty-state title="Belum ada distributor terverifikasi" message="Distributor yang disetujui admin akan muncul di sini." />
        @endforelse
        <div class="mt-3">{{ $verifiedDistributors->links() }}</div>
    </div>

    <div id="tab-rejected" class="hidden space-y-3">
        @forelse ($rejectedDistributors as $distributor)
            <div class="ag-card flex items-center gap-3 p-5">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-50 font-black text-red-700">{{ substr($distributor->name, 0, 1) }}</div>
                <div>
                    <p class="font-black text-slate-900">{{ $distributor->name }}</p>
                    <p class="text-xs font-semibold text-slate-500">{{ $distributor->distributorProfile->company_name ?? '-' }}</p>
                </div>
            </div>
        @empty
            <x-ui.empty-state title="Belum ada pengajuan ditolak" message="Pengajuan distributor yang ditolak akan muncul di sini." />
        @endforelse
        <div class="mt-3">{{ $rejectedDistributors->links() }}</div>
    </div>

    <style>
        .vt-btn { padding: 8px 16px; border-radius: 999px; font-size: .8rem; font-weight: 900; border: 1px solid #e2e8f0; color: #64748b; background: #fff; transition: .2s; }
        .vt-btn:hover, .vt-btn.active { background: #047857; color: #fff; border-color: #047857; }
    </style>
    <script>
        function showTab(name, btn) {
            ['pending', 'verified', 'rejected'].forEach((tab) => document.getElementById('tab-' + tab).classList.add('hidden'));
            document.getElementById('tab-' + name).classList.remove('hidden');
            document.querySelectorAll('.vt-btn').forEach((item) => item.classList.remove('active'));
            btn.classList.add('active');
        }
    </script>
</x-layouts.app>
