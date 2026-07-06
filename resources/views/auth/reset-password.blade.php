@extends('layouts.guest')

@section('title', 'Reset Password - Agrilink')

@section('content')
    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-2xl shadow-emerald-950/15 md:p-8">
        <div class="text-center">
            <p class="ag-label">Password Baru</p>
            <h1 class="mt-3 text-3xl font-black text-slate-950">Buat Password Baru</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan password yang kuat dan mudah kamu ingat.</p>
        </div>

        @if ($errors->any())
            <x-ui.alert type="danger" title="Password belum tersimpan" class="mt-6">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Email</span>
                <input type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="ag-input @error('email') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                <x-ui.field-error name="email" />
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Password Baru</span>
                <input type="password" name="password" required autocomplete="new-password" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" class="ag-input @error('password') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                <x-ui.field-error name="password" />
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Konfirmasi Password</span>
                <input type="password" name="password_confirmation" required autocomplete="new-password" aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}" class="ag-input @error('password_confirmation') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                <x-ui.field-error name="password_confirmation" />
            </label>

            <button type="submit" class="ag-btn-primary w-full rounded-2xl py-4" data-loading-text="Menyimpan...">
                Simpan Password Baru
            </button>
        </form>
    </div>
@endsection
