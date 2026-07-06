<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Agrilink')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-emerald-950 text-slate-900">
    <main class="auth-agri-bg min-h-screen overflow-x-hidden">
        <div class="auth-agri-lines" aria-hidden="true"></div>
        <div class="auth-agri-nodes" aria-hidden="true"></div>

        <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-4xl flex-col px-4 py-8 sm:px-6 lg:px-8">
            <header class="flex items-center justify-center pb-6" data-reveal>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-3 rounded-full border border-white/20 bg-white/95 px-4 py-2 shadow-xl shadow-emerald-950/25">
                    <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-emerald-50 ring-1 ring-emerald-100">
                        <img src="{{ asset('images/agrilink_logo.webp') }}" alt="Logo Agrilink" class="h-7 w-7 object-contain">
                    </span>
                    <span class="text-lg font-black text-slate-950">Agrilink</span>
                </a>
            </header>

            <section class="mx-auto flex w-full flex-1 items-center justify-center pb-8" data-reveal>
                <div class="w-full">
                    @yield('content')
                </div>
            </section>
        </div>
    </main>
</body>
</html>
