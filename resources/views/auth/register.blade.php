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
            <p class="mt-2 text-sm leading-6 text-slate-500">Pilih peran dan lengkapi data dasar untuk mengakses dashboard.</p>
        </div>

        <div id="rolePreview" class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-center">
            <p class="text-xs font-black uppercase text-emerald-700">Preview akses</p>
            <p class="mt-2 text-base font-black text-slate-950" id="roleTitle">Pilih jenis akun</p>
            <p class="mt-1 text-sm leading-6 text-slate-500" id="roleDesc">Setiap role membuka dashboard dan workflow yang berbeda.</p>
        </div>

        @if ($errors->any())
            <div role="alert" class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
            @csrf

            <section class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                <h2 class="text-sm font-black uppercase text-slate-700">Data Akun</h2>
                <div class="mt-4 grid gap-4">
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Jenis Akun</span>
                        <select name="role" id="role-select" required class="ag-select">
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
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Email</span>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com" class="ag-input">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">No. HP</span>
                            <input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="08xxxxxxxxxx" class="ag-input">
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Password</span>
                            <input type="password" name="password" id="regPassword" required autocomplete="new-password" placeholder="Minimal 8 karakter" class="ag-input">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Konfirmasi Password</span>
                            <input type="password" name="password_confirmation" id="regPasswordConfirmation" required autocomplete="new-password" placeholder="Ulangi password" class="ag-input">
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-4">
                <h2 class="text-sm font-black uppercase text-slate-700">Data Pribadi</h2>
                <label class="mt-4 block">
                    <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Nama Lengkap</span>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama sesuai identitas" class="ag-input">
                </label>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-4">
                <h2 class="text-sm font-black uppercase text-slate-700">Alamat dan Lokasi</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">Isi koordinat sesuai lokasi lahan atau distributor.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Latitude</span>
                        <input type="number" step="any" name="latitude" value="{{ old('latitude') }}" placeholder="-6.200000" class="ag-input">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Longitude</span>
                        <input type="number" step="any" name="longitude" value="{{ old('longitude') }}" placeholder="106.816666" class="ag-input">
                    </label>
                </div>
            </section>

            <section id="farmer-fields" class="{{ old('role') == 'farmer' ? '' : 'hidden' }} rounded-3xl border border-emerald-200 bg-emerald-50 p-4">
                <h2 class="text-sm font-black uppercase text-emerald-800">Data Petani</h2>
                <p class="mt-1 text-xs font-semibold text-emerald-700">Data akan digunakan untuk proses verifikasi. NIK harus 16 digit.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-emerald-800">NIK</span>
                        <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16" inputmode="numeric" pattern="[0-9]{16}" autocomplete="off" placeholder="16 digit" data-farmer-required {{ old('role') == 'farmer' ? 'required' : '' }} class="ag-input">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-emerald-800">ID Kelompok Tani</span>
                        <input type="text" name="farmer_group_id" value="{{ old('farmer_group_id') }}" autocomplete="off" placeholder="Opsional" class="ag-input">
                    </label>
                </div>
            </section>

            <section id="distributor-fields" class="{{ old('role') == 'distributor' ? '' : 'hidden' }} rounded-3xl border border-sky-200 bg-sky-50 p-4">
                <h2 class="text-sm font-black uppercase text-sky-800">Data Distributor</h2>
                <p class="mt-1 text-xs font-semibold text-sky-700">Data distributor dapat digunakan untuk proses verifikasi stok dan legalitas.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-sky-800">Nama Perusahaan</span>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Contoh: CV Pupuk Tani" class="ag-input">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-sky-800">Nomor Izin</span>
                        <input type="text" name="license_number" value="{{ old('license_number') }}" placeholder="Nomor izin distributor" class="ag-input">
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
            farmer: ['Dashboard Petani', 'Kelola produk, pantau harga, ajukan pupuk subsidi, dan lihat pesanan masuk.'],
            buyer: ['Dashboard Pembeli', 'Belanja hasil tani, kelola keranjang, dan pantau status pesanan.'],
            distributor: ['Dashboard Distributor', 'Kelola stok pupuk dan proses permintaan subsidi dari petani.']
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

            const copy = roleCopy[role] || ['Pilih jenis akun', 'Setiap role membuka dashboard dan workflow yang berbeda.'];
            if (roleTitle) roleTitle.textContent = copy[0];
            if (roleDesc) roleDesc.textContent = copy[1];
        }

        roleSelect?.addEventListener('change', syncRole);
        syncRole();
    </script>
@endsection
