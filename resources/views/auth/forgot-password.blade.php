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
            <x-ui.alert type="success" class="mt-6">
                {{ session('status') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert type="danger" title="Reset password gagal" class="mt-6">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="nama@email.com" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="ag-input @error('email') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                <x-ui.field-error name="email" />
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
