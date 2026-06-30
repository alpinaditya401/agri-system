@props(['height' => '420px', 'endpoint' => null, 'liveTrack' => false])

@php
    $mapId = 'map_' . uniqid();
    $trackId = $mapId . '_track';
    $src = $endpoint ?? route('api.map.combined');
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .leaflet-container {
        font-family: 'Outfit', ui-sans-serif, system-ui, sans-serif;
        background: #ecfdf5;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 18px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
    }
    .leaflet-popup-content {
        margin: 12px;
    }
    .leaflet-control-zoom a {
        color: #047857 !important;
    }
    .agri-livetrack-item {
        width: 100%;
        text-align: left;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #fff;
        padding: 0.85rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }
    .agri-livetrack-item:hover,
    .agri-livetrack-item.is-active {
        border-color: #86efac;
        background: #f0fdf4;
        box-shadow: 0 10px 18px rgba(16, 185, 129, 0.08);
    }
    .agri-livetrack-item:focus {
        outline: 2px solid #86efac;
        outline-offset: 2px;
    }
    .agri-livetrack-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .agri-livetrack-title {
        margin: 0;
        color: #0f172a;
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.25rem;
    }
    .agri-livetrack-meta {
        margin: 0.25rem 0 0;
        color: #64748b;
        font-size: 0.75rem;
        line-height: 1.1rem;
    }
    .agri-livetrack-badge {
        flex-shrink: 0;
        border-radius: 9999px;
        padding: 0.125rem 0.5rem;
        font-size: 0.625rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .agri-livetrack-badge.product {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .agri-livetrack-badge.farmer {
        background: #dcfce7;
        color: #15803d;
    }
    .agri-livetrack-badge.distributor {
        background: #fef3c7;
        color: #b45309;
    }
</style>

<div class="{{ $liveTrack ? 'grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_340px]' : '' }}">
    <div class="min-w-0">
        <div id="{{ $mapId }}" style="height: {{ $height }};" class="w-full rounded-[1.5rem] border border-slate-200 z-0"></div>

        <div class="mt-4 flex flex-wrap items-center gap-3 text-xs font-bold text-slate-500">
            <span class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700"><span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Petani</span>
            <span class="flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-amber-700"><span class="inline-block h-2.5 w-2.5 rounded-full bg-amber-500"></span> Distributor</span>
            <span class="flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1.5 text-sky-700"><span class="inline-block h-2.5 w-2.5 rounded-full bg-sky-500"></span> Produk</span>
        </div>
    </div>

    @if ($liveTrack)
        <aside id="{{ $trackId }}" class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase text-emerald-700 tracking-[0.18em]">LiveTrack</p>
                    <h3 class="mt-1 text-base font-black text-slate-950">Titik Aktif</h3>
                </div>
                <span class="rounded-full bg-white px-2.5 py-1 text-xs font-black text-emerald-700 shadow-sm" data-track-count>0</span>
            </div>
            <div class="mt-4 max-h-[500px] space-y-2 overflow-y-auto pr-1" data-track-list></div>
            <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white p-4 text-sm font-semibold text-slate-500" data-track-empty>Memuat titik peta...</div>
        </aside>
    @endif
</div>

<script>
(function () {
    const mapElement = document.getElementById(@json($mapId));
    if (!mapElement || typeof L === 'undefined') return;

    const indonesiaBounds = L.latLngBounds(
        L.latLng(-11.2, 94.5),
        L.latLng(6.5, 141.2)
    );

    const map = L.map(mapElement, {
        maxBounds: indonesiaBounds,
        maxBoundsViscosity: 1.0,
        minZoom: 4,
    });

    map.fitBounds(indonesiaBounds, { padding: [12, 12] });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18,
        noWrap: true,
        bounds: indonesiaBounds,
    }).addTo(map);

    const colors = { farmer: '#10b981', distributor: '#f59e0b', product: '#3b82f6' };
    const trackRoot = document.getElementById(@json($trackId));
    const trackList = trackRoot ? trackRoot.querySelector('[data-track-list]') : null;
    const trackEmpty = trackRoot ? trackRoot.querySelector('[data-track-empty]') : null;
    const trackCount = trackRoot ? trackRoot.querySelector('[data-track-count]') : null;
    const canLiveTrack = @json((bool) $liveTrack) && trackRoot && trackList;
    const bounds = [];
    const trackEntries = [];
    let activeTrackButton = null;
    const placeholderImage = @json(asset('images/commodities/placeholder.svg'));

    fetch(@json($src))
        .then(r => r.json())
        .then(geojson => {
            (geojson.features || []).forEach(f => {
                if (!f.geometry || !Array.isArray(f.geometry.coordinates)) return;

                const [lngRaw, latRaw] = f.geometry.coordinates;
                const lng = Number(lngRaw);
                const lat = Number(latRaw);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
                if (!indonesiaBounds.contains([lat, lng])) return;

                const p = f.properties || {};
                const color = colors[p.layer] || '#10b981';
                const marker = L.circleMarker([lat, lng], {
                    radius: 8, color: '#fff', weight: 2, fillColor: color, fillOpacity: 0.9,
                }).addTo(map);

                marker.bindPopup(buildPopup(p));
                bounds.push([lat, lng]);
                trackEntries.push({ marker, latlng: [lat, lng], properties: p });
            });

            renderTrack(trackEntries);
        })
        .catch(() => {
            if (trackEmpty) {
                trackEmpty.textContent = 'Data peta belum bisa dimuat.';
                trackEmpty.classList.remove('hidden');
            }
        });

    setTimeout(() => map.invalidateSize(), 120);

    function buildPopup(p) {
        let popup = `<div style="font-family:'Outfit',sans-serif;min-width:220px;max-width:280px">`;

        if (p.layer === 'product' || p.layer === 'farmer') {
            popup += popupImage(p.image, featureTitle(p));
        }

        popup += `<p style="font-weight:800;font-size:13px;margin:0 0 6px;color:#111827;">${escMap(featureTitle(p))}</p>`;

        if (p.layer === 'farmer') {
            popup += row('Komoditas', p.main_commodity || '-');
            popup += row('Kelompok', p.farmer_group || '-');
            popup += row('Lokasi', compactLocation(p));
            popup += productMiniList(p.products);
        } else if (p.layer === 'distributor') {
            popup += row('Distributor', p.company_name || p.name || '-');
            popup += row('Lokasi', compactLocation(p));

            const stockList = Array.isArray(p.stock) ? p.stock : [];
            popup += stockList.length
                ? stockList.map(s => row(s.type || 'Pupuk', `${formatNumber(s.available_kg || 0)} kg`)).join('')
                : row('Stok Pupuk', 'Kosong');
        } else if (p.layer === 'product') {
            popup += row('Harga', formatPrice(p.price, p.unit));
            popup += row('Stok', formatStock(p.stock, p.unit));
            popup += row('Petani', p.farmer_name || '-');
            popup += row('Kategori', p.category || '-');
            popup += row('Lokasi', compactLocation(p));

            if (p.url) {
                popup += `<a href="${escAttr(p.url)}" style="display:inline-flex;margin-top:8px;padding:6px 10px;border-radius:999px;background:#059669;color:#fff;font-size:11px;font-weight:700;text-decoration:none;">Lihat Produk</a>`;
            }
        }

        return popup + `</div>`;
    }

    function popupImage(src, alt) {
        const imageSrc = src || placeholderImage;

        return `<div style="height:104px;margin:0 0 10px;border-radius:14px;overflow:hidden;background:#ecfdf5;border:1px solid #d1fae5;">
            <img src="${escAttr(imageSrc)}" alt="${escAttr(alt || 'Komoditas')}" loading="lazy" onerror="this.onerror=null;this.src='${escAttr(placeholderImage)}';" style="width:100%;height:100%;object-fit:cover;display:block;">
        </div>`;
    }

    function productMiniList(products) {
        const list = Array.isArray(products) ? products : [];
        if (!list.length) return '';

        const items = list.slice(0, 3).map(product => {
            const name = escMap(product.name || 'Produk');
            const meta = `${formatPrice(product.price, product.unit)} | ${formatStock(product.stock, product.unit)}`;
            const url = product.url ? escAttr(product.url) : null;
            const label = url ? `<a href="${url}" style="color:#047857;font-weight:800;text-decoration:none;">${name}</a>` : `<span style="font-weight:800;color:#374151;">${name}</span>`;

            return `<li style="margin:0 0 4px;color:#4b5563;font-size:11px;line-height:1.35;">${label}<br><span>${escMap(meta)}</span></li>`;
        }).join('');

        return `<div style="margin-top:8px;padding-top:8px;border-top:1px solid #e5e7eb;">
            <p style="margin:0 0 5px;color:#374151;font-size:11px;font-weight:800;">Produk aktif</p>
            <ul style="margin:0;padding-left:16px;">${items}</ul>
        </div>`;
    }

    function renderTrack(entries) {
        if (!canLiveTrack) return;

        const sorted = entries.slice().sort((a, b) => layerOrder(a.properties.layer) - layerOrder(b.properties.layer));
        trackList.innerHTML = '';
        if (trackCount) trackCount.textContent = formatNumber(sorted.length);

        if (!sorted.length) {
            if (trackEmpty) {
                trackEmpty.textContent = 'Belum ada titik peta.';
                trackEmpty.classList.remove('hidden');
            }
            return;
        }

        if (trackEmpty) trackEmpty.classList.add('hidden');
        sorted.forEach(entry => trackList.appendChild(createTrackButton(entry)));
    }

    function createTrackButton(entry) {
        const p = entry.properties;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'agri-livetrack-item';

        const top = document.createElement('div');
        top.className = 'agri-livetrack-top';

        const text = document.createElement('div');
        const title = document.createElement('p');
        title.className = 'agri-livetrack-title';
        title.textContent = featureTitle(p);

        const meta = document.createElement('p');
        meta.className = 'agri-livetrack-meta';
        meta.textContent = featureMeta(p);

        const badge = document.createElement('span');
        badge.className = `agri-livetrack-badge ${p.layer || 'farmer'}`;
        badge.textContent = layerLabel(p.layer);

        text.append(title, meta);
        top.append(text, badge);
        button.append(top);
        button.addEventListener('click', () => {
            if (activeTrackButton) activeTrackButton.classList.remove('is-active');
            activeTrackButton = button;
            button.classList.add('is-active');
            map.setView(entry.latlng, Math.max(map.getZoom(), 10), { animate: true });
            entry.marker.openPopup();
        });

        return button;
    }

    function row(label, value) {
        if (value === undefined || value === null || value === '') return '';
        return `<p style="margin:0 0 5px;color:#4b5563;font-size:11px;line-height:1.35;"><span style="font-weight:700;color:#374151;">${escMap(label)}:</span> ${escMap(value)}</p>`;
    }

    function featureTitle(p) {
        if (p.layer === 'distributor') return p.company_name || p.name || 'Distributor';
        if (p.layer === 'farmer') return p.name || 'Petani';
        return p.name || 'Produk Tani';
    }

    function featureMeta(p) {
        if (p.layer === 'product') return `${formatPrice(p.price, p.unit)} | Stok ${formatStock(p.stock, p.unit)}`;
        if (p.layer === 'distributor') return compactLocation(p) || 'Distributor pupuk';
        return p.main_commodity ? `Komoditas ${p.main_commodity}` : (compactLocation(p) || 'Petani terverifikasi');
    }

    function layerLabel(layer) {
        if (layer === 'product') return 'Produk';
        if (layer === 'distributor') return 'Pupuk';
        return 'Petani';
    }

    function layerOrder(layer) {
        return { product: 0, farmer: 1, distributor: 2 }[layer] ?? 3;
    }

    function compactLocation(p) {
        return [p.district, p.province].filter(Boolean).join(', ');
    }

    function formatPrice(value, unit) {
        return `Rp ${formatNumber(value || 0)}/${unit || 'unit'}`;
    }

    function formatStock(value, unit) {
        return `${formatNumber(value || 0)} ${unit || ''}`.trim();
    }

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    function escMap(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function escAttr(s) {
        return escMap(s).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
})();
</script>
