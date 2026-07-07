@extends('layouts.guest')

@section('title', 'Lupa Password - Agrilink')

@section('content')
    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-2xl shadow-emerald-950/15 md:p-8">
        <div class="text-center">
            <p class="ag-label">Reset Password</p>
            <h1 class="mt-3 text-3xl font-black text-slate-950">Verifikasi Akun</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Isi data sesuai akun Agrilink. Jika semua cocok, link reset password akan langsung muncul di halaman ini.</p>
        </div>

        @if (session('status'))
            <x-ui.alert type="success" class="mt-6">
                {{ session('status') }}
            </x-ui.alert>
        @endif

        @if (session('reset_link'))
            <div class="mt-6 rounded-3xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs font-black uppercase text-emerald-700">Link Reset Password</p>
                <p class="mt-1 text-sm font-semibold leading-6 text-emerald-800">Link ini muncul karena data verifikasi cocok. Buka link untuk membuat password baru.</p>
                <div class="mt-3 break-all rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-xs font-bold text-slate-700">
                    {{ session('reset_link') }}
                </div>
                <a href="{{ session('reset_link') }}" class="ag-btn-primary mt-4 inline-flex w-full justify-center rounded-2xl py-3 text-sm">
                    Buka Form Reset Password
                </a>
            </div>
        @endif

        @if ($errors->any())
            <x-ui.alert type="danger" title="Reset password gagal" class="mt-6">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
            @csrf

            <section class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                <h2 class="text-sm font-black uppercase text-slate-700">Data Akun</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="nama@email.com" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="ag-input @error('email') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                        <x-ui.field-error name="email" />
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-xs font-bold uppercase text-slate-500">No. HP</span>
                        <input type="text" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" data-digits-only placeholder="08xxxxxxxxxx" aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" class="ag-input @error('phone') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                        <x-ui.field-error name="phone" />
                    </label>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-4">
                <h2 class="text-sm font-black uppercase text-slate-700">Data Pribadi</h2>
                <label class="mt-4 block">
                    <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Nama Lengkap</span>
                    <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Nama sesuai data akun" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" class="ag-input @error('name') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                    <x-ui.field-error name="name" />
                </label>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-4">
                <x-location-picker
                    id="forgot-password-location-picker"
                    title="Lokasi"
                    description="Untuk Pembeli, lokasi boleh dikosongkan. Untuk Petani/Distributor, pilih wilayah lahan atau gudang agar data dapat divalidasi."
                    :province-value="old('province')"
                    :district-value="old('district')"
                    :sub-district-value="old('sub_district')"
                    :village-value="old('village')"
                    :include-address="false"
                    :include-coordinates="false"
                />
            </section>

            <button type="submit" class="ag-btn-primary w-full rounded-2xl py-4" data-loading-text="Mengirim...">
                Validasi dan Tampilkan Link
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            Sudah ingat password?
            <a href="{{ route('login') }}" class="font-black text-emerald-700 hover:text-emerald-800">Masuk</a>
        </p>
    </div>
@endsection
