<x-layouts.app :title="'Daftar Jadi Distributor - Agrilink'">
    <x-slot:sidebar>
        @include('buyer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading">Daftar Jadi Distributor</h1>
        <p class="mt-1 text-sm text-slate-500">Ajukan akun pembeli Anda menjadi distributor pupuk subsidi. Admin akan memverifikasi data usaha dan gudang.</p>
    </x-slot:header>

    @php
        $profile = $user->distributorProfile;
        $status = $profile?->verification_status;
    @endphp

    @if ($profile)
        <div class="mb-5 rounded-3xl border {{ $status === 'verified' ? 'border-emerald-200 bg-emerald-50' : ($status === 'rejected' ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50') }} p-5">
            <p class="text-xs font-black uppercase tracking-wide {{ $status === 'verified' ? 'text-emerald-700' : ($status === 'rejected' ? 'text-red-700' : 'text-amber-700') }}">
                Status pengajuan: {{ $status === 'verified' ? 'Terverifikasi' : ($status === 'rejected' ? 'Ditolak' : 'Menunggu admin') }}
            </p>
            <p class="mt-2 text-sm font-semibold text-slate-700">
                {{ $status === 'verified'
                    ? 'Akun Anda sudah aktif sebagai distributor.'
                    : ($status === 'rejected'
                        ? 'Pengajuan sebelumnya ditolak. Anda dapat memperbarui data dan mengirim ulang.'
                        : 'Pengajuan Anda sedang ditinjau admin. Notifikasi akan muncul setelah diproses.') }}
            </p>
        </div>
    @endif

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <form method="POST" action="{{ route('buyer.become-distributor.store') }}" class="ag-card space-y-5 p-5 md:p-6">
            @csrf

            <section>
                <p class="ag-label">Data usaha</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-black uppercase tracking-wide text-slate-500">Nama usaha / distributor</span>
                        <input type="text" name="company_name" value="{{ old('company_name', $profile->company_name ?? '') }}" required class="ag-input mt-1" placeholder="CV Tani Makmur">
                        @error('company_name') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs font-black uppercase tracking-wide text-slate-500">Nomor izin distributor</span>
                        <input type="text" name="license_number" value="{{ old('license_number', $profile->license_number ?? '') }}" required class="ag-input mt-1" placeholder="SIUP/NIB/izin distributor">
                        @error('license_number') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs font-black uppercase tracking-wide text-slate-500">Kapasitas gudang (kg)</span>
                        <input type="number" name="storage_capacity_kg" value="{{ old('storage_capacity_kg', $profile->storage_capacity_kg ?? '') }}" required min="100" class="ag-input mt-1" placeholder="5000">
                        @error('storage_capacity_kg') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs font-black uppercase tracking-wide text-slate-500">No. HP</span>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" data-digits-only class="ag-input mt-1" placeholder="08xxxxxxxxxx">
                        @error('phone') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                    </label>
                </div>
            </section>

            <section>
                <p class="ag-label">Alamat dan lokasi gudang</p>
                <div class="mt-4">
                    <x-location-picker
                        id="become-distributor-location-picker"
                        title="Wilayah Gudang"
                        description="Pilih wilayah gudang sampai desa/kelurahan. Tulis gang, RT/RW, nomor gudang, dan patokan pada alamat detail."
                        :province-value="old('province', $user->province)"
                        :district-value="old('district', $user->district)"
                        :sub-district-value="old('sub_district', $user->sub_district)"
                        :village-value="old('village', $user->village)"
                        :address-value="old('address', $user->address)"
                        :latitude-value="old('latitude', $user->latitude)"
                        :longitude-value="old('longitude', $user->longitude)"
                        :required="true"
                        :address-required="true"
                    />
                </div>
            </section>

            <button type="submit" class="ag-btn-primary w-full justify-center">
                {{ $profile ? 'Kirim Ulang Pengajuan' : 'Kirim Pengajuan Distributor' }}
            </button>
        </form>

        <aside class="ag-card h-fit p-5">
            <p class="ag-label">Alur verifikasi</p>
            <div class="mt-4 space-y-3">
                @foreach ([
                    'Isi data usaha dan gudang distributor.',
                    'Admin menerima notifikasi pengajuan.',
                    'Admin memeriksa izin, kapasitas, dan wilayah layanan.',
                    'Jika disetujui, role akun berubah menjadi Distributor.',
                ] as $step)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-600">{{ $step }}</div>
                @endforeach
            </div>
        </aside>
    </div>
</x-layouts.app>
