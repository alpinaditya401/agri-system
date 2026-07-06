@props([
    'id' => null,
    'provinceName' => 'province',
    'districtName' => 'district',
    'subDistrictName' => 'sub_district',
    'villageName' => 'village',
    'addressName' => 'address',
    'latitudeName' => 'latitude',
    'longitudeName' => 'longitude',
    'provinceValue' => '',
    'districtValue' => '',
    'subDistrictValue' => '',
    'villageValue' => '',
    'addressValue' => '',
    'latitudeValue' => '',
    'longitudeValue' => '',
    'includeSubDistrict' => true,
    'includeVillage' => true,
    'includeAddress' => true,
    'includeCoordinates' => true,
    'required' => false,
    'dynamicRequired' => false,
    'addressRequired' => false,
    'title' => 'Lokasi',
    'description' => 'Pilih wilayah administrasi Indonesia sampai desa/kelurahan. Detail gang, RT/RW, dan nomor rumah tetap diisi manual.',
])

@php
    $pickerId = $id ?: 'location-picker-' . uniqid();
    $hasCoordinateValue = filled($latitudeValue) && filled($longitudeValue);
@endphp

<div id="{{ $pickerId }}" class="space-y-4" data-location-picker>
    <div>
        <h2 class="text-sm font-black uppercase text-slate-700">{{ $title }}</h2>
        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">{{ $description }}</p>
        <p data-location-status class="mt-2 hidden rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700"></p>
    </div>

    <input type="hidden" name="{{ $provinceName }}" value="{{ $provinceValue }}" data-location-hidden="province">
    <input type="hidden" name="{{ $districtName }}" value="{{ $districtValue }}" data-location-hidden="district">
    @if ($includeSubDistrict)
        <input type="hidden" name="{{ $subDistrictName }}" value="{{ $subDistrictValue }}" data-location-hidden="sub_district">
    @endif
    @if ($includeVillage)
        <input type="hidden" name="{{ $villageName }}" value="{{ $villageValue }}" data-location-hidden="village">
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Provinsi</span>
            <select class="ag-select @error($provinceName) border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror"
                data-location-select="province"
                data-selected-name="{{ $provinceValue }}"
                aria-invalid="{{ $errors->has($provinceName) ? 'true' : 'false' }}"
                @if ($required) required @endif
                @if ($dynamicRequired) data-location-required @endif>
                <option value="">Memuat provinsi...</option>
            </select>
            <x-ui.field-error :name="$provinceName" />
        </label>

        <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Kabupaten/Kota</span>
            <select class="ag-select @error($districtName) border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror"
                data-location-select="district"
                data-selected-name="{{ $districtValue }}"
                aria-invalid="{{ $errors->has($districtName) ? 'true' : 'false' }}"
                @if ($required) required @endif
                @if ($dynamicRequired) data-location-required @endif>
                <option value="">Pilih provinsi dahulu</option>
            </select>
            <x-ui.field-error :name="$districtName" />
        </label>

        @if ($includeSubDistrict)
            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Kecamatan</span>
                <select class="ag-select @error($subDistrictName) border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror"
                    data-location-select="sub_district"
                    data-selected-name="{{ $subDistrictValue }}"
                    aria-invalid="{{ $errors->has($subDistrictName) ? 'true' : 'false' }}"
                    @if ($required) required @endif
                    @if ($dynamicRequired) data-location-required @endif>
                    <option value="">Pilih kabupaten/kota dahulu</option>
                </select>
                <x-ui.field-error :name="$subDistrictName" />
            </label>
        @endif

        @if ($includeVillage)
            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Desa/Kelurahan</span>
                <select class="ag-select @error($villageName) border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror"
                    data-location-select="village"
                    data-selected-name="{{ $villageValue }}"
                    aria-invalid="{{ $errors->has($villageName) ? 'true' : 'false' }}"
                    @if ($required) required @endif
                    @if ($dynamicRequired) data-location-required @endif>
                    <option value="">Pilih kecamatan dahulu</option>
                </select>
                <x-ui.field-error :name="$villageName" />
            </label>
        @endif
    </div>

    @if ($includeAddress)
        <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Alamat Detail</span>
            <textarea name="{{ $addressName }}" rows="3"
                placeholder="Contoh: Jl. Tani Makmur, Gang Melati, RT 02/RW 05, No. 12"
                class="ag-input resize-none @error($addressName) border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror"
                aria-invalid="{{ $errors->has($addressName) ? 'true' : 'false' }}"
                @if ($addressRequired) required @endif>{{ $addressValue }}</textarea>
            <p class="mt-1 text-xs font-semibold leading-5 text-slate-400">Isi manual untuk gang, RT/RW, nomor rumah/gudang, patokan, atau nama jalan kecil.</p>
            <x-ui.field-error :name="$addressName" />
        </label>
    @endif

    @if ($includeCoordinates)
        <input type="hidden" name="{{ $latitudeName }}" value="{{ $latitudeValue }}" data-location-coordinate="latitude">
        <input type="hidden" name="{{ $longitudeName }}" value="{{ $longitudeValue }}" data-location-coordinate="longitude">

        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase text-slate-500">Titik Lokasi Peta</p>
                    <p class="mt-1 text-xs font-semibold text-slate-400">Opsional. Pakai tombol ini kalau ingin titik peta lebih akurat.</p>
                    <p data-location-coordinate-summary class="mt-2 text-xs font-bold {{ $hasCoordinateValue ? 'text-emerald-700' : 'text-slate-400' }}">
                        {{ $hasCoordinateValue ? 'Titik lokasi sudah diisi.' : 'Belum memakai titik lokasi perangkat.' }}
                    </p>
                    <x-ui.field-error :name="$latitudeName" />
                    <x-ui.field-error :name="$longitudeName" />
                </div>
                <button type="button" class="ag-btn-secondary px-4 py-2 text-xs" data-location-current>
                    Gunakan Lokasi Saat Ini
                </button>
            </div>
        </div>
    @endif
</div>

<script>
    (() => {
        const root = document.getElementById(@json($pickerId));
        if (!root || root.dataset.locationBound === '1') return;
        root.dataset.locationBound = '1';

        const API_BASE = 'https://wilayah.id/api';
        const status = root.querySelector('[data-location-status]');
        const selects = {
            province: root.querySelector('[data-location-select="province"]'),
            district: root.querySelector('[data-location-select="district"]'),
            subDistrict: root.querySelector('[data-location-select="sub_district"]'),
            village: root.querySelector('[data-location-select="village"]'),
        };
        const hidden = {
            province: root.querySelector('[data-location-hidden="province"]'),
            district: root.querySelector('[data-location-hidden="district"]'),
            subDistrict: root.querySelector('[data-location-hidden="sub_district"]'),
            village: root.querySelector('[data-location-hidden="village"]'),
        };

        const endpoints = {
            provinces: `${API_BASE}/provinces.json`,
            regencies: (provinceCode) => `${API_BASE}/regencies/${provinceCode}.json`,
            districts: (regencyCode) => `${API_BASE}/districts/${regencyCode}.json`,
            villages: (districtCode) => `${API_BASE}/villages/${districtCode}.json`,
        };

        const normalize = (value) => (value || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/\b(kabupaten|kab\.|kota administrasi|kota|administrasi)\b/g, '')
            .replace(/[^a-z0-9]+/g, ' ')
            .trim();

        const namesMatch = (left, right) => {
            const a = normalize(left);
            const b = normalize(right);
            return a && b && (a === b || a.includes(b) || b.includes(a));
        };

        const showStatus = (message, tone = 'warning') => {
            if (!status) return;
            status.textContent = message;
            status.classList.remove('hidden', 'border-amber-200', 'bg-amber-50', 'text-amber-700', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
            if (tone === 'success') {
                status.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
            } else {
                status.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-700');
            }
        };

        const hideStatus = () => status?.classList.add('hidden');

        const optionName = (select) => select?.selectedOptions?.[0]?.dataset?.name || '';

        const setHiddenValue = (key) => {
            if (!hidden[key]) return;
            const select = key === 'subDistrict' ? selects.subDistrict : selects[key];
            hidden[key].value = optionName(select);
        };

        const resetSelect = (select, placeholder) => {
            if (!select) return;
            select.innerHTML = `<option value="">${placeholder}</option>`;
            select.disabled = false;
        };

        const resetBelow = (level) => {
            if (level === 'province') {
                resetSelect(selects.district, 'Pilih provinsi dahulu');
                resetSelect(selects.subDistrict, 'Pilih kabupaten/kota dahulu');
                resetSelect(selects.village, 'Pilih kecamatan dahulu');
                if (hidden.district) hidden.district.value = '';
                if (hidden.subDistrict) hidden.subDistrict.value = '';
                if (hidden.village) hidden.village.value = '';
            }
            if (level === 'district') {
                resetSelect(selects.subDistrict, 'Pilih kabupaten/kota dahulu');
                resetSelect(selects.village, 'Pilih kecamatan dahulu');
                if (hidden.subDistrict) hidden.subDistrict.value = '';
                if (hidden.village) hidden.village.value = '';
            }
            if (level === 'subDistrict') {
                resetSelect(selects.village, 'Pilih kecamatan dahulu');
                if (hidden.village) hidden.village.value = '';
            }
        };

        async function loadOptions(select, url, placeholder, selectedName = '') {
            if (!select) return false;

            select.disabled = true;
            select.innerHTML = `<option value="">Memuat...</option>`;

            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Gagal mengambil data wilayah');

                const json = await response.json();
                const rows = Array.isArray(json.data) ? json.data : [];

                select.innerHTML = `<option value="">${placeholder}</option>`;
                rows.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.code;
                    option.dataset.name = item.name;
                    option.textContent = item.name;
                    select.appendChild(option);
                });

                const wantedName = selectedName || select.dataset.selectedName || '';
                if (wantedName) {
                    const match = rows.find((item) => namesMatch(item.name, wantedName));
                    if (match) {
                        select.value = match.code;
                    } else {
                        const option = document.createElement('option');
                        option.value = '__current';
                        option.dataset.name = wantedName;
                        option.textContent = `${wantedName} (tersimpan)`;
                        option.selected = true;
                        select.appendChild(option);
                    }
                }

                select.disabled = false;
                hideStatus();
                return true;
            } catch (error) {
                select.innerHTML = `<option value="">API wilayah belum bisa diakses</option>`;
                select.disabled = true;
                showStatus('Data wilayah belum bisa dimuat. Cek koneksi internet, lalu muat ulang halaman.');
                return false;
            }
        }

        async function init() {
            await loadOptions(selects.province, endpoints.provinces, 'Pilih provinsi', hidden.province?.value);
            setHiddenValue('province');

            if (selects.province?.value && selects.province.value !== '__current') {
                await loadOptions(selects.district, endpoints.regencies(selects.province.value), 'Pilih kabupaten/kota', hidden.district?.value);
                setHiddenValue('district');
            }

            if (selects.district?.value && selects.district.value !== '__current') {
                await loadOptions(selects.subDistrict, endpoints.districts(selects.district.value), 'Pilih kecamatan', hidden.subDistrict?.value);
                setHiddenValue('subDistrict');
            }

            if (selects.subDistrict?.value && selects.subDistrict.value !== '__current') {
                await loadOptions(selects.village, endpoints.villages(selects.subDistrict.value), 'Pilih desa/kelurahan', hidden.village?.value);
                setHiddenValue('village');
            }
        }

        selects.province?.addEventListener('change', async () => {
            setHiddenValue('province');
            resetBelow('province');
            if (selects.province.value && selects.province.value !== '__current') {
                await loadOptions(selects.district, endpoints.regencies(selects.province.value), 'Pilih kabupaten/kota');
            }
        });

        selects.district?.addEventListener('change', async () => {
            setHiddenValue('district');
            resetBelow('district');
            if (selects.district.value && selects.district.value !== '__current') {
                await loadOptions(selects.subDistrict, endpoints.districts(selects.district.value), 'Pilih kecamatan');
            }
        });

        selects.subDistrict?.addEventListener('change', async () => {
            setHiddenValue('subDistrict');
            resetBelow('subDistrict');
            if (selects.subDistrict.value && selects.subDistrict.value !== '__current') {
                await loadOptions(selects.village, endpoints.villages(selects.subDistrict.value), 'Pilih desa/kelurahan');
            }
        });

        selects.village?.addEventListener('change', () => setHiddenValue('village'));

        root.querySelector('[data-location-current]')?.addEventListener('click', () => {
            if (!navigator.geolocation) {
                showStatus('Browser tidak mendukung deteksi lokasi.');
                return;
            }

            showStatus('Mengambil lokasi perangkat...', 'success');
            navigator.geolocation.getCurrentPosition((position) => {
                const latInput = root.querySelector('[data-location-coordinate="latitude"]');
                const lngInput = root.querySelector('[data-location-coordinate="longitude"]');
                const coordinateSummary = root.querySelector('[data-location-coordinate-summary]');
                if (latInput) latInput.value = position.coords.latitude.toFixed(8);
                if (lngInput) lngInput.value = position.coords.longitude.toFixed(8);
                if (coordinateSummary) {
                    coordinateSummary.textContent = 'Titik lokasi sudah diisi dari perangkat.';
                    coordinateSummary.classList.remove('text-slate-400');
                    coordinateSummary.classList.add('text-emerald-700');
                }
                showStatus(`Lokasi berhasil diisi. Akurasi sekitar ${Math.round(position.coords.accuracy || 0)} meter.`, 'success');
            }, () => {
                showStatus('Lokasi gagal diambil. Pastikan izin lokasi aktif di browser.');
            }, { enableHighAccuracy: true, timeout: 12000 });
        });

        init();
    })();
</script>
