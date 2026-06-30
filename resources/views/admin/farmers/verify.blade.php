<x-layouts.app :title="'Verifikasi Petani – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Verifikasi Petani
        </h1>
        <p class="text-gray-500 text-sm">Tinjau dan setujui pengajuan verifikasi akun petani.</p>
    </x-slot:header>

    <div class="flex gap-2 mb-5" id="verifyTabs">
        <button onclick="showTab('pending', this)" class="vt-btn active">Pending ({{ $pendingFarmers->total() }})</button>
        <button onclick="showTab('verified', this)" class="vt-btn">Terverifikasi ({{ $verifiedFarmers->total() }})</button>
        <button onclick="showTab('rejected', this)" class="vt-btn">Ditolak ({{ $rejectedFarmers->total() }})</button>
    </div>

    <div id="tab-pending" class="space-y-3">
        @forelse ($pendingFarmers as $farmer)
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-700 font-bold">{{ substr($farmer->name, 0, 1) }}</div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm">{{ $farmer->name }}</p>
                        <p class="text-xs text-gray-500">NIK: {{ $farmer->farmerProfile->nik ?? '-' }} · {{ $farmer->farmerProfile->farmer_group_name ?? 'Kelompok belum diisi' }}</p>
                        <p class="text-xs text-gray-400">{{ $farmer->district ?? '-' }}, {{ $farmer->province ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.farmers.verify.approve', $farmer) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-xl text-xs transition-colors">Setujui</button>
                    </form>
                    <button onclick="document.getElementById('reject-form-{{ $farmer->id }}').classList.toggle('hidden')" class="bg-red-50 hover:bg-red-100 text-red-600 font-semibold px-4 py-2 rounded-xl text-xs transition-colors">Tolak</button>
                </div>
                <form id="reject-form-{{ $farmer->id }}" method="POST" action="{{ route('admin.farmers.verify.reject', $farmer) }}" class="hidden w-full flex gap-2 mt-2">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="rejection_reason" required placeholder="Alasan penolakan..."
                           class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-xl text-xs transition-colors">Kirim</button>
                </form>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
                <p class="text-gray-400 text-sm">Tidak ada verifikasi tertunda.</p>
            </div>
        @endforelse
        <div class="mt-3">{{ $pendingFarmers->links() }}</div>
    </div>

    <div id="tab-verified" class="space-y-3 hidden">
        @forelse ($verifiedFarmers as $farmer)
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-700 font-bold">{{ substr($farmer->name, 0, 1) }}</div>
                <div>
                    <p class="font-bold text-gray-800 text-sm">{{ $farmer->name }}</p>
                    <p class="text-xs text-gray-500">Diverifikasi {{ $farmer->farmerProfile->verified_at?->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
                <p class="text-gray-400 text-sm">Belum ada petani terverifikasi.</p>
            </div>
        @endforelse
        <div class="mt-3">{{ $verifiedFarmers->links() }}</div>
    </div>

    <div id="tab-rejected" class="space-y-3 hidden">
        @forelse ($rejectedFarmers as $farmer)
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-red-100 rounded-full flex items-center justify-center text-red-700 font-bold">{{ substr($farmer->name, 0, 1) }}</div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm">{{ $farmer->name }}</p>
                        <p class="text-xs text-red-500">{{ $farmer->farmerProfile->rejection_reason ?? '-' }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
                <p class="text-gray-400 text-sm">Belum ada penolakan.</p>
            </div>
        @endforelse
        <div class="mt-3">{{ $rejectedFarmers->links() }}</div>
    </div>

    <style>
        .vt-btn { padding:7px 16px; border-radius:40px; font-size:.8rem; font-weight:700; border:1.5px solid #e2e8f0; color:#64748b; background:#fff; cursor:pointer; transition:.2s; }
        .vt-btn:hover, .vt-btn.active { background:#047857; color:#fff; border-color:#047857; }
    </style>
    <script>
        function showTab(name, btn) {
            ['pending', 'verified', 'rejected'].forEach(t => document.getElementById('tab-' + t).classList.add('hidden'));
            document.getElementById('tab-' + name).classList.remove('hidden');
            document.querySelectorAll('.vt-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
    </script>
</x-layouts.app>
