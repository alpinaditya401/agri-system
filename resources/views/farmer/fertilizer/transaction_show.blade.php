<x-layouts.app :title="'Detail Transaksi Pupuk – Agrilink'">
    <x-slot:sidebar>
        @include('farmer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">{{ $transaction->transaction_number }}</h1>
        <p class="text-gray-500 text-sm">Diajukan {{ $transaction->created_at->translatedFormat('d F Y, H:i') }}</p>
    </x-slot:header>

    @if (in_array($transaction->status, ['approved', 'dispensed'], true))
        @push('head')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        @endpush
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <h2 class="font-bold text-gray-800 mb-4">Detail Permintaan</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-xs text-gray-400">Jenis Pupuk</p><p class="font-semibold text-gray-800">{{ $transaction->fertilizerType->name ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-400">Distributor</p><p class="font-semibold text-gray-800">{{ $transaction->distributor->name ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-400">Jumlah Diminta</p><p class="font-semibold text-gray-800">{{ $transaction->requested_kg }} kg</p></div>
                <div><p class="text-xs text-gray-400">Jumlah Disetujui</p><p class="font-semibold text-gray-800">{{ $transaction->approved_kg ?? '-' }} kg</p></div>
                <div><p class="text-xs text-gray-400">Harga per kg</p><p class="font-semibold text-gray-800">Rp {{ number_format($transaction->price_per_kg, 0, ',', '.') }}</p></div>
                <div><p class="text-xs text-gray-400">Total</p><p class="font-semibold text-gray-800">Rp {{ number_format($transaction->total_amount ?? 0, 0, ',', '.') }}</p></div>
            </div>

            @if ($transaction->rejection_reason)
                <div class="mt-4 bg-red-50 border border-red-100 rounded-xl p-3">
                    <p class="text-xs font-semibold text-red-700">Alasan Ditolak:</p>
                    <p class="text-sm text-red-600 mt-1">{{ $transaction->rejection_reason }}</p>
                </div>
            @endif
        </div>

        @if (in_array($transaction->status, ['approved', 'dispensed'], true))
            <div class="lg:col-span-2 lg:row-start-2 bg-white rounded-2xl border border-emerald-100 p-5 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-emerald-700">Live Tracking</p>
                        <h2 class="mt-1 font-bold text-gray-800">Posisi Distributor</h2>
                        <p class="mt-1 text-sm text-gray-500">Pantau posisi terakhir distributor saat pupuk sedang dikirim atau diserahkan.</p>
                    </div>
                    <span id="farmerTrackingBadge" class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">Belum Aktif</span>
                </div>

                <div id="farmerTrackingMap" class="mt-4 h-[340px] rounded-2xl border border-slate-200 bg-emerald-50"></div>

                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-xs font-bold uppercase text-slate-400">Distributor</p>
                        <p class="mt-1 text-sm font-black text-slate-800">{{ $transaction->distributor->name ?? '-' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-xs font-bold uppercase text-slate-400">Koordinat</p>
                        <p id="farmerTrackingCoordinates" class="mt-1 text-sm font-black text-slate-800">Belum ada lokasi</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-xs font-bold uppercase text-slate-400">Update</p>
                        <p id="farmerTrackingUpdated" class="mt-1 text-sm font-black text-slate-800">-</p>
                    </div>
                </div>

                <p id="farmerTrackingHelper" class="mt-3 text-xs font-semibold text-slate-400">Menunggu lokasi terbaru dari distributor...</p>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm h-fit lg:col-start-3 lg:row-start-1">
            <h2 class="font-bold text-gray-800 mb-3">Status</h2>
            @php
                $badge = match($transaction->status) {
                    'pending' => 'bg-yellow-50 text-yellow-700',
                    'approved' => 'bg-blue-50 text-blue-700',
                    'dispensed' => 'bg-emerald-50 text-emerald-700',
                    'rejected', 'cancelled' => 'bg-red-50 text-red-700',
                    default => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <span class="text-xs font-semibold px-2.5 py-1 rounded-md {{ $badge }}">{{ ucfirst($transaction->status) }}</span>

            @if ($transaction->status === 'pending')
                <form method="POST" action="{{ route('farmer.fertilizer.transactions.cancel', $transaction) }}" class="mt-4" onsubmit="return confirm('Batalkan permintaan ini?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2.5 rounded-xl text-sm transition-colors">Batalkan Permintaan</button>
                </form>
            @endif
        </div>
    </div>

    @if (in_array($transaction->status, ['approved', 'dispensed'], true))
        @push('scripts')
            <script>
                (() => {
                    const endpoint = @json(route('api.fertilizer-tracking.show', $transaction));
                    const mapEl = document.getElementById('farmerTrackingMap');
                    if (!mapEl || typeof L === 'undefined') return;

                    const fallbackCenter = [-2.5, 118.0];
                    const farmerLat = Number(@json($transaction->farmer?->latitude));
                    const farmerLng = Number(@json($transaction->farmer?->longitude));
                    const initialCenter = Number.isFinite(farmerLat) && Number.isFinite(farmerLng) ? [farmerLat, farmerLng] : fallbackCenter;

                    const map = L.map(mapEl, { minZoom: 4 }).setView(initialCenter, Number.isFinite(farmerLat) ? 12 : 5);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 18,
                    }).addTo(map);

                    let distributorMarker = null;
                    let routeLine = null;
                    const badge = document.getElementById('farmerTrackingBadge');
                    const coords = document.getElementById('farmerTrackingCoordinates');
                    const updated = document.getElementById('farmerTrackingUpdated');
                    const helper = document.getElementById('farmerTrackingHelper');

                    if (Number.isFinite(farmerLat) && Number.isFinite(farmerLng)) {
                        L.circleMarker([farmerLat, farmerLng], {
                            radius: 9,
                            color: '#fff',
                            weight: 2,
                            fillColor: '#10b981',
                            fillOpacity: 0.95,
                        }).addTo(map).bindPopup('Lokasi tujuan petani');
                    }

                    async function loadTracking() {
                        try {
                            const response = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
                            const json = await response.json();
                            if (!response.ok || json.status !== 'success') throw new Error(json.message || 'Tracking belum bisa dimuat.');

                            const tracking = json.data.tracking;
                            updateBadge(tracking.status, tracking.status_label);

                            if (!tracking.has_location) {
                                if (helper) helper.textContent = 'Distributor belum mengaktifkan live tracking.';
                                return;
                            }

                            const latlng = [tracking.latitude, tracking.longitude];
                            if (!distributorMarker) {
                                distributorMarker = L.circleMarker(latlng, {
                                    radius: 10,
                                    color: '#fff',
                                    weight: 2,
                                    fillColor: '#f59e0b',
                                    fillOpacity: 0.95,
                                }).addTo(map);
                            } else {
                                distributorMarker.setLatLng(latlng);
                            }

                            distributorMarker.bindPopup(`Distributor: ${json.data.distributor.name || '-'}`);
                            if (coords) coords.textContent = `${tracking.latitude}, ${tracking.longitude}`;
                            if (updated) updated.textContent = tracking.updated_human || 'baru saja';
                            if (helper) helper.textContent = tracking.note || `Lokasi terakhir diperbarui ${tracking.updated_human || 'baru saja'}.`;

                            const bounds = [latlng];
                            if (Number.isFinite(farmerLat) && Number.isFinite(farmerLng)) {
                                bounds.push([farmerLat, farmerLng]);
                                if (routeLine) map.removeLayer(routeLine);
                                routeLine = L.polyline([latlng, [farmerLat, farmerLng]], {
                                    color: '#059669',
                                    weight: 3,
                                    dashArray: '8 8',
                                }).addTo(map);
                            }
                            map.fitBounds(bounds, { padding: [36, 36], maxZoom: 14 });
                        } catch (error) {
                            if (helper) helper.textContent = error.message || 'Tracking belum bisa dimuat.';
                        }
                    }

                    function updateBadge(status, label) {
                        if (!badge) return;
                        badge.textContent = label || 'Belum Aktif';
                        badge.className = 'w-fit rounded-full px-3 py-1 text-xs font-black ';
                        badge.className += status === 'arrived'
                            ? 'bg-sky-100 text-sky-700'
                            : status === 'nearby'
                                ? 'bg-amber-100 text-amber-700'
                                : status === 'paused'
                                    ? 'bg-slate-100 text-slate-600'
                                    : status === 'on_the_way'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-slate-100 text-slate-600';
                    }

                    loadTracking();
                    setInterval(loadTracking, 8000);
                    setTimeout(() => map.invalidateSize(), 150);
                })();
            </script>
        @endpush
    @endif
</x-layouts.app>
