<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Agrilink')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    @php($authVideoExists = file_exists(public_path('videos/hero-agriculture.mp4')))

    <main class="relative min-h-screen overflow-x-hidden bg-slate-900">
        <div class="absolute inset-0">
            @if ($authVideoExists)
                <video autoplay muted loop playsinline preload="metadata" poster="{{ asset('images/landing/sawah.webp') }}" class="h-full w-full object-cover">
                    <source src="{{ asset('videos/hero-agriculture.mp4') }}" type="video/mp4">
                </video>
            @else
                <img src="{{ asset('images/landing/sawah.webp') }}" alt="Lanskap pertanian Agrilink" class="h-full w-full object-cover">
            @endif
        </div>

        <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-4xl flex-col px-4 py-8 sm:px-6 lg:px-8">
            <header class="flex items-center justify-center pb-6" data-reveal>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-3 rounded-full border border-white/70 bg-white/95 px-4 py-2 shadow-xl shadow-slate-950/10 backdrop-blur">
                    <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-white">
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
