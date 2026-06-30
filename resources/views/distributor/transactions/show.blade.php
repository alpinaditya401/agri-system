<x-layouts.app :title="'Detail Permintaan – Agrilink'">
    <x-slot:sidebar>
        @include('distributor._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">{{ $transaction->transaction_number }}</h1>
        <p class="text-gray-500 text-sm">Diajukan {{ $transaction->created_at->translatedFormat('d F Y, H:i') }}</p>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h2 class="font-bold text-gray-800 mb-4">Detail Permintaan</h2>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><p class="text-xs text-gray-400">Petani</p><p class="font-semibold text-gray-800">{{ $transaction->farmer->name ?? '-' }}</p></div>
                    <div><p class="text-xs text-gray-400">NIK</p><p class="font-semibold text-gray-800">{{ $transaction->farmer->farmerProfile->nik ?? '-' }}</p></div>
                    <div><p class="text-xs text-gray-400">Jenis Pupuk</p><p class="font-semibold text-gray-800">{{ $transaction->fertilizerType->name ?? '-' }}</p></div>
                    <div><p class="text-xs text-gray-400">Jumlah Diminta</p><p class="font-semibold text-gray-800">{{ $transaction->requested_kg }} kg</p></div>
                    <div><p class="text-xs text-gray-400">Harga per kg</p><p class="font-semibold text-gray-800">Rp {{ number_format($transaction->price_per_kg, 0, ',', '.') }}</p></div>
                    <div><p class="text-xs text-gray-400">Sisa Kuota Petani</p><p class="font-semibold text-gray-800">{{ number_format($transaction->quota->remaining_kg ?? 0) }} kg</p></div>
                </div>
            </div>

            @if (in_array($transaction->status, ['approved', 'dispensed'], true))
                <div class="bg-white rounded-2xl border border-emerald-100 p-5 shadow-sm">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-wide text-emerald-700">Live Tracking</p>
                            <h2 class="mt-1 font-bold text-gray-800">Lokasi Distributor</h2>
                            <p class="mt-1 text-sm text-gray-500">Aktifkan saat pengiriman pupuk berjalan agar petani dapat melihat posisi terakhir Anda.</p>
                        </div>
                        <span id="trackingStatusBadge" class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                            {{ $transaction->tracking_status ? ucfirst(str_replace('_', ' ', $transaction->tracking_status)) : 'Belum Aktif' }}
                        </span>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        <button type="button" data-tracking-status="on_the_way" class="tracking-status-btn rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-black text-emerald-700 hover:bg-emerald-100">Dalam Perjalanan</button>
                        <button type="button" data-tracking-status="nearby" class="tracking-status-btn rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-black text-amber-700 hover:bg-amber-100">Mendekati</button>
                        <button type="button" data-tracking-status="arrived" class="tracking-status-btn rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-xs font-black text-sky-700 hover:bg-sky-100">Sampai Lokasi</button>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($transaction->status === 'approved')
                            <button type="button" id="startTrackingBtn" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Mulai Live Tracking</button>
                            <button type="button" id="stopTrackingBtn" class="hidden bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Jeda Tracking</button>
                        @endif
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
                        <p><span class="font-bold text-slate-800">Koordinat terakhir:</span> <span id="trackingCoordinates">{{ $transaction->tracking_latitude && $transaction->tracking_longitude ? $transaction->tracking_latitude . ', ' . $transaction->tracking_longitude : 'Belum ada lokasi' }}</span></p>
                        <p class="mt-1"><span class="font-bold text-slate-800">Diperbarui:</span> <span id="trackingUpdatedAt">{{ $transaction->tracking_updated_at?->diffForHumans() ?? '-' }}</span></p>
                        <p id="trackingHelper" class="mt-2 text-xs font-semibold text-slate-400">Geolocation berjalan di browser. Di hosting, fitur ini membutuhkan HTTPS.</p>
                    </div>
                </div>
            @endif

            @if ($transaction->status === 'pending')
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <h2 class="font-bold text-gray-800 mb-4">Setujui Permintaan</h2>
                    <form method="POST" action="{{ route('distributor.fertilizer.approve', $transaction) }}" class="flex items-end gap-3">
                        @csrf
                        @method('PATCH')
                        <div class="flex-1">
                            <label class="text-xs font-semibold text-gray-500">Jumlah Disetujui (kg)</label>
                            <input type="number" name="approved_kg" max="{{ $transaction->requested_kg }}" value="{{ $transaction->requested_kg }}" min="1" required
                                   class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                        </div>
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Setujui</button>
                    </form>

                    <form method="POST" action="{{ route('distributor.fertilizer.reject', $transaction) }}" class="mt-4 space-y-2">
                        @csrf
                        @method('PATCH')
                        <label class="text-xs font-semibold text-gray-500">Atau Tolak dengan Alasan</label>
                        <textarea name="rejection_reason" rows="2" placeholder="Tulis alasan penolakan..."
                                  class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-red-400"></textarea>
                        <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Tolak Permintaan</button>
                    </form>
                </div>
            @endif

            @if ($transaction->status === 'approved')
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <h2 class="font-bold text-gray-800 mb-3">Serahkan Pupuk</h2>
                    <p class="text-sm text-gray-500 mb-4">Disetujui sebanyak <strong>{{ $transaction->approved_kg }} kg</strong>. Tandai sebagai diserahkan setelah pupuk diambil/dikirim ke petani.</p>
                    <form method="POST" action="{{ route('distributor.fertilizer.dispense', $transaction) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Tandai Diserahkan</button>
                    </form>
                </div>
            @endif

            @if ($transaction->rejection_reason)
                <div class="bg-red-50 border border-red-100 rounded-2xl p-4">
                    <p class="text-xs font-semibold text-red-700">Alasan Ditolak:</p>
                    <p class="text-sm text-red-600 mt-1">{{ $transaction->rejection_reason }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-5">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h2 class="font-bold text-gray-800 mb-3">Status</h2>
                @php
                    $badge = match($transaction->status) {
                        'pending' => 'bg-yellow-50 text-yellow-700',
                        'approved' => 'bg-blue-50 text-blue-700',
                        'dispensed' => 'bg-emerald-50 text-emerald-700',
                        'rejected' => 'bg-red-50 text-red-700',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="text-xs font-semibold px-2.5 py-1 rounded-md {{ $badge }}">{{ ucfirst($transaction->status) }}</span>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h2 class="font-bold text-gray-800 mb-3">Petani</h2>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-700 font-bold">{{ substr($transaction->farmer->name ?? 'P', 0, 1) }}</div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $transaction->farmer->name ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $transaction->farmer->district ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ route('chat.index', ['target' => $transaction->farmer_id]) }}" class="mt-3 block text-center bg-gray-50 hover:bg-emerald-50 text-emerald-700 font-semibold py-2 rounded-xl text-xs transition-colors">
                    Hubungi Petani
                </a>
            </div>
        </div>
    </div>

    @if (in_array($transaction->status, ['approved', 'dispensed'], true))
        @push('scripts')
            <script>
                (() => {
                    const updateUrl = @json(route('api.fertilizer-tracking.update', $transaction));
                    const csrfToken = @json(csrf_token());
                    const statusLabels = {
                        on_the_way: 'Dalam Perjalanan',
                        nearby: 'Mendekati Lokasi',
                        arrived: 'Sampai Lokasi',
                        paused: 'Tracking Dijeda',
                    };
                    let watcherId = null;
                    let currentStatus = @json($transaction->tracking_status ?: 'on_the_way');

                    const helper = document.getElementById('trackingHelper');
                    const badge = document.getElementById('trackingStatusBadge');
                    const coords = document.getElementById('trackingCoordinates');
                    const updatedAt = document.getElementById('trackingUpdatedAt');
                    const startBtn = document.getElementById('startTrackingBtn');
                    const stopBtn = document.getElementById('stopTrackingBtn');

                    document.querySelectorAll('.tracking-status-btn').forEach((button) => {
                        button.addEventListener('click', () => {
                            currentStatus = button.dataset.trackingStatus || 'on_the_way';
                            updateBadge();
                            if (helper) helper.textContent = `Status tracking diubah: ${statusLabels[currentStatus]}.`;
                        });
                    });

                    startBtn?.addEventListener('click', () => {
                        if (!navigator.geolocation) {
                            if (helper) helper.textContent = 'Browser tidak mendukung geolocation.';
                            return;
                        }

                        if (watcherId !== null) return;

                        if (helper) helper.textContent = 'Meminta izin lokasi dan memulai live tracking...';
                        watcherId = navigator.geolocation.watchPosition(sendPosition, () => {
                            if (helper) helper.textContent = 'Lokasi gagal dikirim. Pastikan izin lokasi aktif.';
                        }, { enableHighAccuracy: true, maximumAge: 10000, timeout: 15000 });

                        startBtn.classList.add('hidden');
                        stopBtn?.classList.remove('hidden');
                    });

                    stopBtn?.addEventListener('click', () => {
                        if (watcherId !== null) {
                            navigator.geolocation.clearWatch(watcherId);
                            watcherId = null;
                        }
                        currentStatus = 'paused';
                        updateBadge();
                        if (helper) helper.textContent = 'Live tracking dijeda di browser ini.';
                        stopBtn.classList.add('hidden');
                        startBtn?.classList.remove('hidden');
                    });

                    async function sendPosition(position) {
                        try {
                            const payload = {
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                                accuracy: position.coords.accuracy,
                                tracking_status: currentStatus,
                            };

                            const response = await fetch(updateUrl, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify(payload),
                            });

                            const json = await response.json();
                            if (!response.ok || json.status !== 'success') {
                                throw new Error(json.message || 'Lokasi belum bisa dikirim.');
                            }

                            const tracking = json.data.tracking;
                            if (coords) coords.textContent = `${tracking.latitude}, ${tracking.longitude}`;
                            if (updatedAt) updatedAt.textContent = tracking.updated_human || 'baru saja';
                            if (helper) helper.textContent = `Lokasi terkirim. Akurasi sekitar ${Math.round(tracking.accuracy || 0)} meter.`;
                            updateBadge(tracking.status);
                        } catch (error) {
                            if (helper) helper.textContent = error.message || 'Lokasi belum bisa dikirim.';
                        }
                    }

                    function updateBadge(status = currentStatus) {
                        if (!badge) return;
                        badge.textContent = statusLabels[status] || 'Belum Aktif';
                        badge.className = 'w-fit rounded-full px-3 py-1 text-xs font-black ';
                        badge.className += status === 'arrived'
                            ? 'bg-sky-100 text-sky-700'
                            : status === 'nearby'
                                ? 'bg-amber-100 text-amber-700'
                                : status === 'paused'
                                    ? 'bg-slate-100 text-slate-600'
                                    : 'bg-emerald-100 text-emerald-700';
                    }

                    updateBadge();
                })();
            </script>
        @endpush
    @endif
</x-layouts.app>
