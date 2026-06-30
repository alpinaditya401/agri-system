@extends('layouts.guest')

@section('title', 'Lupa Password - Agrilink')

@section('content')
    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-2xl shadow-emerald-950/15 md:p-8">
        <div class="text-center">
            <p class="ag-label">Reset Password</p>
            <h1 class="mt-3 text-3xl font-black text-slate-950">Lupa Password?</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Masukkan email akun Agrilink. Link reset akan dikirim ke email tersebut.</p>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="nama@email.com" class="ag-input">
            </label>

            <button type="submit" class="ag-btn-primary w-full rounded-2xl py-4" data-loading-text="Mengirim...">
                Kirim Link Reset
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            Sudah ingat password?
            <a href="{{ route('login') }}" class="font-black text-emerald-700 hover:text-emerald-800">Masuk</a>
        </p>
    </div>
@endsection
