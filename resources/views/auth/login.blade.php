@extends('layouts.guest')

@section('title', 'Masuk - Agrilink')

@section('content')
    <div class="mx-auto max-w-xl rounded-[2rem] border border-white/20 bg-white/95 p-6 shadow-2xl shadow-emerald-950/25 md:p-8">
        <div class="text-center">
            <p class="ag-label">Masuk Akun</p>
            <h1 class="mt-3 text-3xl font-black text-slate-950">Selamat Datang</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan email dan password akun Agrilink.</p>
        </div>

        @if (session('status') || session('success'))
            <x-ui.alert type="success" class="mt-6">
                {{ session('status') ?? session('success') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert type="danger" title="Login gagal" class="mt-6">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="nama@email.com" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="ag-input @error('email') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                <x-ui.field-error name="email" />
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Password</span>
                <div class="relative">
                    <input type="password" name="password" id="passInput" required autocomplete="current-password" placeholder="Masukkan password" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" class="ag-input pr-12 @error('password') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                    <button type="button" onclick="togglePass('passInput')" aria-label="Tampilkan atau sembunyikan password" class="absolute right-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-emerald-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 12S5.5 5 12 5s9.5 7 9.5 7-3 7-9.5 7-9.5-7-9.5-7Z" /><circle cx="12" cy="12" r="3" stroke-width="2" /></svg>
                    </button>
                </div>
                <x-ui.field-error name="password" />
            </label>

            <div class="flex items-center justify-between gap-3 text-sm">
                <label class="inline-flex items-center gap-2 font-semibold text-slate-500">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    Ingat saya
                </label>
                <a href="{{ route('password.request') }}" class="font-black text-emerald-700 hover:text-emerald-800">Lupa password?</a>
            </div>

            <button type="submit" class="ag-btn-primary w-full rounded-2xl py-4" data-loading-text="Masuk...">
                Masuk Sekarang
            </button>
        </form>

        <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-black uppercase text-slate-500">Akun Demo</p>
                <p class="text-[11px] font-black text-emerald-700">Password: password</p>
            </div>
            <div class="mt-3 grid gap-2">
                @foreach ([
                    ['label' => 'Buyer', 'email' => 'buyer.demo@agri.com', 'tone' => 'bg-emerald-100 text-emerald-700'],
                    ['label' => 'Petani', 'email' => 'petani.karawang@agri.com', 'tone' => 'bg-lime-100 text-lime-700'],
                    ['label' => 'Distributor', 'email' => 'distributor.demo@agri.com', 'tone' => 'bg-sky-100 text-sky-700'],
                    ['label' => 'Admin', 'email' => 'admin@agri.com', 'tone' => 'bg-amber-100 text-amber-700'],
                    ['label' => 'Admin Master', 'email' => 'admin.master@agri.com', 'tone' => 'bg-slate-200 text-slate-800'],
                ] as $demo)
                    <button type="button" data-demo-email="{{ $demo['email'] }}" data-demo-password="password" class="demo-account flex min-h-11 items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-left transition hover:border-emerald-200 hover:bg-emerald-50">
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $demo['tone'] }}">{{ $demo['label'] }}</span>
                        <span class="min-w-0 truncate text-xs font-bold text-slate-500">{{ $demo['email'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <p class="mt-6 text-center text-sm text-slate-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-black text-emerald-700 hover:text-emerald-800">Daftar sekarang</a>
        </p>
    </div>

    <script>
        function togglePass(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        document.querySelectorAll('.demo-account').forEach((button) => {
            button.addEventListener('click', () => {
                const email = document.querySelector('input[name="email"]');
                const password = document.querySelector('input[name="password"]');
                if (email && password) {
                    email.value = button.dataset.demoEmail;
                    password.value = button.dataset.demoPassword;
                    password.focus();
                }
            });
        });
    </script>
@endsection
