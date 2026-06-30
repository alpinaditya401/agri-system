<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Agrilink' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            min-height: 2.75rem;
            padding: .7rem .85rem;
            border-radius: 1rem;
            color: rgb(209 250 229 / .82);
            font-size: .9rem;
            font-weight: 700;
            transition: .18s ease;
        }
        .sidebar-link:hover {
            background: rgb(255 255 255 / .10);
            color: #fff;
        }
        .sidebar-link.active {
            background: linear-gradient(135deg, rgb(16 185 129), rgb(5 150 105));
            color: #fff;
            box-shadow: 0 14px 30px rgb(4 120 87 / .28);
        }
        .sidebar-link svg {
            opacity: .95;
        }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    </style>
    @stack('head')
</head>
<body class="ag-dashboard-bg min-h-screen overflow-x-hidden">
    @php
        $currentUser = auth()->user();
        $currentUserPhoto = $currentUser?->profile_photo_url;
        $currentUserInitial = strtoupper(substr($currentUser?->name ?? 'U', 0, 1));
    @endphp
    <div class="md:hidden fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-emerald-950/95 px-4 py-3 text-white shadow-lg backdrop-blur-xl">
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white">
                    <img src="{{ asset('images/agrilink_logo.webp') }}" alt="Logo Agrilink" class="h-7 w-7 object-contain" onerror="this.style.display='none'">
                </span>
                <span class="text-lg font-black">Agrilink</span>
            </a>
            <div class="flex items-center gap-2">
                @include('components.notification-bell')
                <button id="mobileMenuBtn" type="button" class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white transition hover:bg-white/15" aria-label="Buka menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="sidebarOverlay" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm md:hidden"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col overflow-hidden border-r border-white/10 bg-emerald-950 text-white shadow-2xl shadow-emerald-950/30 transition-transform duration-300 md:translate-x-0">
        <div class="relative flex min-h-20 items-center gap-3 border-b border-white/10 px-5">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm">
                <img src="{{ asset('images/agrilink_logo.webp') }}" alt="Logo Agrilink" class="h-8 w-8 object-contain" onerror="this.style.display='none'">
            </span>
            <div class="min-w-0">
                <p class="text-lg font-black leading-none">Agrilink</p>
                <p class="mt-1 text-xs font-semibold text-emerald-100/70">Platform Agritech Indonesia</p>
            </div>
            <button id="mobileCloseBtn" type="button" class="ml-auto flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-emerald-50 md:hidden" aria-label="Tutup menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="ag-no-scrollbar flex-1 space-y-1 overflow-y-auto px-4 py-5">
            {{ $sidebar ?? '' }}

            <div class="mt-5 space-y-1 border-t border-white/10 pt-5">
                <a href="{{ route('chat.index') }}" class="sidebar-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.4-4 8-9 8a10 10 0 0 1-4.3-1L3 20l1.4-3.7A7 7 0 0 1 3 12c0-4.4 4-8 9-8s9 3.6 9 8Z" /></svg>
                    <span class="whitespace-nowrap">Pesan / Chat</span>
                </a>
                <a href="{{ route('notifications.index') }}" class="sidebar-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.7V5a2 2 0 1 0-4 0v.3A6 6 0 0 0 6 11v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" /></svg>
                    <span class="whitespace-nowrap">Notifikasi</span>
                    <span id="sidebarNotifDot" class="ml-auto hidden rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-black text-white">0</span>
                </a>
                <a href="{{ route('public.map') }}" class="sidebar-link {{ request()->routeIs('public.map') ? 'active' : '' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 20-5.4-2.7A1 1 0 0 1 3 16.4V5.6a1 1 0 0 1 1.4-.9L9 7m0 13 6-3m-6 3V7m6 10 4.6 2.3a1 1 0 0 0 1.4-.9V7.6a1 1 0 0 0-.6-.9L15 4m0 13V4m0 0L9 7" /></svg>
                    <span class="whitespace-nowrap">Peta Distribusi</span>
                </a>
                <a href="{{ route('public.prices') }}" class="sidebar-link {{ request()->routeIs('public.prices') ? 'active' : '' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8M8 13h5m-8 7h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z" /></svg>
                    <span class="whitespace-nowrap">Harga BPS</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z" /></svg>
                    <span class="whitespace-nowrap">Profil</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" data-loading="off">
                    @csrf
                    <button type="submit" class="flex min-h-11 w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left text-sm font-bold text-red-200 transition hover:bg-red-500/15 hover:text-white">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m17 16 4-4m0 0-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1" /></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </nav>

        <div class="border-t border-white/10 p-4">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-3xl border border-white/10 bg-white/10 p-3 transition hover:bg-white/15">
                @if ($currentUserPhoto)
                    <img src="{{ $currentUserPhoto }}" alt="Foto profil {{ $currentUser?->name }}" class="h-11 w-11 flex-shrink-0 rounded-2xl object-cover ring-1 ring-white/20">
                @else
                    <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-emerald-400 text-sm font-black text-emerald-950">{{ $currentUserInitial }}</span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-sm font-bold text-white">{{ $currentUser?->name }}</span>
                    <span class="block truncate text-xs font-semibold text-emerald-100/70">{{ $currentUser?->role?->display_name }}</span>
                </span>
            </a>
        </div>
    </aside>

    <main class="min-h-screen w-full px-4 pb-10 pt-24 md:ml-72 md:w-[calc(100%-18rem)] md:px-6 md:pt-6 lg:px-8">
        <div class="mx-auto w-full max-w-[1500px]">
            <div class="mb-6 flex flex-col gap-4 rounded-3xl border border-white/70 bg-white/80 p-4 shadow-sm backdrop-blur-xl md:flex-row md:items-center md:justify-between md:px-5">
                <div class="min-w-0">
                    {{ $header ?? '' }}
                </div>
                <div class="hidden items-center gap-3 md:flex">
                    @include('components.notification-bell')
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm transition hover:bg-slate-50">
                        @if ($currentUserPhoto)
                            <img src="{{ $currentUserPhoto }}" alt="Foto profil {{ $currentUser?->name }}" class="h-9 w-9 rounded-xl object-cover">
                        @else
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-700">{{ $currentUserInitial }}</span>
                        @endif
                        <span class="max-w-44 truncate text-sm font-bold text-slate-700">{{ $currentUser?->name }}</span>
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 shadow-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800 shadow-sm">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800 shadow-sm">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}

            <footer class="mt-10 text-center text-xs font-semibold text-slate-400">
                Agrilink - Sistem pertanian digital untuk distribusi, niaga, dan informasi harga.
            </footer>
        </div>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openSidebar = () => {
            sidebar?.classList.remove('-translate-x-full');
            overlay?.classList.remove('hidden');
        };
        const closeSidebar = () => {
            sidebar?.classList.add('-translate-x-full');
            overlay?.classList.add('hidden');
        };

        document.getElementById('mobileMenuBtn')?.addEventListener('click', openSidebar);
        document.getElementById('mobileCloseBtn')?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        async function pollNotifSummary() {
            try {
                const res = await fetch('{{ route("api.notifications.summary") }}');
                const json = await res.json();
                if (json.status !== 'success') return;

                document.querySelectorAll('.js-notif-count').forEach((el) => {
                    el.textContent = json.unread;
                    el.classList.toggle('hidden', json.unread === 0);
                });

                const dot = document.getElementById('sidebarNotifDot');
                if (dot) {
                    dot.textContent = json.unread;
                    dot.classList.toggle('hidden', json.unread === 0);
                }
            } catch (e) {}
        }

        pollNotifSummary();
        setInterval(pollNotifSummary, 20000);
    </script>
    @stack('scripts')
</body>
</html>
