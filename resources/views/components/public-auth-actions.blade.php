@props(['tone' => 'solid', 'showRegister' => false])

@php
    $isGlass = $tone === 'glass';

    $nameClass = $isGlass
        ? 'inline-flex max-w-[12rem] items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-2 text-sm font-semibold text-white/95 backdrop-blur-md transition-all hover:bg-white/15'
        : 'inline-flex max-w-[12rem] items-center gap-2 rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm font-semibold text-white transition-colors hover:bg-white/15';

    $dashboardClass = $isGlass
        ? 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white px-5 py-2.5 rounded-full font-semibold transition-all shadow-lg shadow-emerald-500/30 border border-emerald-400/50 text-sm'
        : 'bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors';

    $loginClass = $isGlass
        ? 'text-white/90 font-medium hover:text-white transition-colors text-sm'
        : 'bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors';

    $registerClass = 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white px-6 py-2.5 rounded-full font-semibold transition-all shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-0.5 border border-emerald-400/50 text-sm';

    $currentUser = auth()->user();
    $currentUserPhoto = $currentUser?->profile_photo_url;
@endphp

<div class="flex items-center gap-2">
    @auth
        <a href="{{ route('profile.edit') }}" class="{{ $nameClass }}" title="{{ auth()->user()->name }}">
            @if ($currentUserPhoto)
                <img src="{{ $currentUserPhoto }}" alt="Foto profil {{ $currentUser?->name }}" class="h-6 w-6 flex-shrink-0 rounded-full object-cover">
            @else
                <span class="h-2 w-2 flex-shrink-0 rounded-full bg-emerald-300"></span>
            @endif
            <span class="truncate">{{ auth()->user()->name }}</span>
        </a>
        <a href="{{ route('dashboard') }}" class="{{ $dashboardClass }}">Dashboard</a>
    @else
        <a href="{{ route('login') }}" class="{{ $loginClass }}">Masuk</a>
        @if ($showRegister)
            <a href="{{ route('register') }}" class="{{ $registerClass }}">Daftar Tani</a>
        @endif
    @endauth
</div>
