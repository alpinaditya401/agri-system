@extends('layouts.guest')

@section('title', 'Daftar - Agrilink')

@section('content')
    @php
        $roleOptions = collect($roles ?? []);
    @endphp

    <div class="mx-auto max-w-3xl rounded-[2rem] border border-slate-200 bg-white p-6 shadow-2xl shadow-emerald-950/15 md:p-8">
        <div class="text-center">
            <p class="ag-label">Buat Akun</p>
            <h1 class="mt-3 text-3xl font-black text-slate-950">Daftar Agrilink</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Akun Pembeli langsung aktif. Akun Petani dan Distributor perlu verifikasi admin sebelum bisa login.</p>
        </div>

        <div id="rolePreview" class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-center">
            <p class="text-xs font-black uppercase text-emerald-700">Preview akses</p>
            <p class="mt-2 text-base font-black text-slate-950" id="roleTitle">Pilih jenis akun</p>
            <p class="mt-1 text-sm leading-6 text-slate-500" id="roleDesc">Setiap role membuka dashboard dan workflow yang berbeda.</p>
        </div>

        @if ($errors->any())
            <x-ui.alert type="danger" title="Registrasi belum berhasil" class="mt-6">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
            @csrf

            <section class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                <h2 class="text-sm font-black uppercase text-slate-700">Data Akun</h2>
                <div class="mt-4 grid gap-4">
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Jenis Akun</span>
                        <select name="role" id="role-select" required aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}" class="ag-select @error('role') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih jenis akun</option>
                            @if($roleOptions->isNotEmpty())
                                @foreach($roleOptions as $role)
                                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->display_name }}</option>
                                @endforeach
                            @else
                                <option value="buyer" {{ old('role') == 'buyer' ? 'selected' : '' }}>Pembeli</option>
                                <option value="farmer" {{ old('role') == 'farmer' ? 'selected' : '' }}>Petani</option>
                                <option value="distributor" {{ old('role') == 'distributor' ? 'selected' : '' }}>Distributor</option>
                            @endif
                        </select>
                        <x-ui.field-error name="role" />
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Email</span>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="ag-input @error('email') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                            <x-ui.field-error name="email" />
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">No. HP</span>
                            <input type="text" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" data-digits-only placeholder="08xxxxxxxxxx" aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" class="ag-input @error('phone') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                            <x-ui.field-error name="phone" />
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Password</span>
                            <input type="password" name="password" id="regPassword" required autocomplete="new-password" placeholder="Minimal 8 karakter" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" class="ag-input @error('password') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                            <x-ui.field-error name="password" />
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Konfirmasi Password</span>
                            <input type="password" name="password_confirmation" id="regPasswordConfirmation" required autocomplete="new-password" placeholder="Ulangi password" aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}" class="ag-input @error('password_confirmation') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                            <x-ui.field-error name="password_confirmation" />
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-4">
                <h2 class="text-sm font-black uppercase text-slate-700">Data Pribadi</h2>
                <label class="mt-4 block">
                    <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Nama Lengkap</span>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama sesuai identitas" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" class="ag-input @error('name') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                    <x-ui.field-error name="name" />
                </label>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-4">
                <x-location-picker
                    id="register-location-picker"
                    title="Lokasi"
                    description="Untuk Pembeli, lokasi boleh dikosongkan saat daftar. Untuk Petani/Distributor, pilih wilayah lahan atau gudang agar admin bisa memverifikasi area Anda."
                    :province-value="old('province')"
                    :district-value="old('district')"
                    :sub-district-value="old('sub_district')"
                    :village-value="old('village')"
                    :address-value="old('address')"
                    :latitude-value="old('latitude')"
                    :longitude-value="old('longitude')"
                    :include-coordinates="false"
                    :dynamic-required="true"
                />
            </section>

            <section id="farmer-fields" class="{{ old('role') == 'farmer' ? '' : 'hidden' }} rounded-3xl border border-emerald-200 bg-emerald-50 p-4">
                <h2 class="text-sm font-black uppercase text-emerald-800">Data Petani</h2>
                <p class="mt-1 text-xs font-semibold text-emerald-700">Data akan digunakan untuk proses verifikasi. NIK harus 16 digit.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-emerald-800">NIK</span>
                        <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16" inputmode="numeric" pattern="[0-9]{16}" data-digits-only autocomplete="off" placeholder="16 digit" data-farmer-required {{ old('role') == 'farmer' ? 'required' : '' }} aria-invalid="{{ $errors->has('nik') ? 'true' : 'false' }}" class="ag-input @error('nik') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                        <x-ui.field-error name="nik" />
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-emerald-800">ID Kelompok Tani</span>
                        <input type="text" name="farmer_group_id" value="{{ old('farmer_group_id') }}" autocomplete="off" placeholder="Opsional" aria-invalid="{{ $errors->has('farmer_group_id') ? 'true' : 'false' }}" class="ag-input @error('farmer_group_id') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                        <x-ui.field-error name="farmer_group_id" />
                    </label>
                </div>
            </section>

            <section id="distributor-fields" class="{{ old('role') == 'distributor' ? '' : 'hidden' }} rounded-3xl border border-sky-200 bg-sky-50 p-4">
                <h2 class="text-sm font-black uppercase text-sky-800">Data Distributor</h2>
                <p class="mt-1 text-xs font-semibold text-sky-700">Data distributor dapat digunakan untuk proses verifikasi stok dan legalitas.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-sky-800">Nama Perusahaan</span>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Contoh: CV Pupuk Tani" data-distributor-required {{ old('role') == 'distributor' ? 'required' : '' }} aria-invalid="{{ $errors->has('company_name') ? 'true' : 'false' }}" class="ag-input @error('company_name') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                        <x-ui.field-error name="company_name" />
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-sky-800">Nomor Izin</span>
                        <input type="text" name="license_number" value="{{ old('license_number') }}" placeholder="Nomor izin distributor" aria-invalid="{{ $errors->has('license_number') ? 'true' : 'false' }}" class="ag-input @error('license_number') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                        <x-ui.field-error name="license_number" />
                    </label>
                </div>
            </section>

            <button type="submit" class="ag-btn-primary w-full rounded-2xl py-4" data-loading-text="Mendaftarkan...">
                Daftar Sekarang
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-black text-emerald-700 hover:text-emerald-800">Masuk</a>
        </p>
    </div>

    <script>
        const roleCopy = {
            farmer: ['Pengajuan Akun Petani', 'Data akan dikirim ke admin. Anda baru bisa login setelah akun petani diverifikasi.'],
            buyer: ['Dashboard Pembeli', 'Akun langsung aktif untuk belanja hasil tani, kelola keranjang, dan pantau pesanan.'],
            distributor: ['Pengajuan Akun Distributor', 'Data akan dikirim ke admin. Anda baru bisa login setelah akun distributor diverifikasi.']
        };

        const roleSelect = document.getElementById('role-select');
        const farmerFields = document.getElementById('farmer-fields');
        const distributorFields = document.getElementById('distributor-fields');
        const roleTitle = document.getElementById('roleTitle');
        const roleDesc = document.getElementById('roleDesc');

        function syncRole() {
            const role = roleSelect?.value;
            const isFarmer = role === 'farmer';
            const isDistributor = role === 'distributor';
            farmerFields?.classList.toggle('hidden', !isFarmer);
            distributorFields?.classList.toggle('hidden', !isDistributor);
            farmerFields?.querySelectorAll('[data-farmer-required]').forEach((input) => {
                input.required = isFarmer;
            });
            document.querySelectorAll('[data-location-required]').forEach((input) => {
                input.required = isFarmer || isDistributor;
            });
            distributorFields?.querySelectorAll('[data-distributor-required]').forEach((input) => {
                input.required = isDistributor;
            });

            const copy = roleCopy[role] || ['Pilih jenis akun', 'Setiap role membuka dashboard dan workflow yang berbeda.'];
            if (roleTitle) roleTitle.textContent = copy[0];
            if (roleDesc) roleDesc.textContent = copy[1];
        }

        roleSelect?.addEventListener('change', syncRole);
        syncRole();
    </script>
@endsection
