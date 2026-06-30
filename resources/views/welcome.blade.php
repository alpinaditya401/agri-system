<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi & Perdagangan Pertanian</title>
    <!-- Tailwind CSS v4 via Browser Build -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-slate-800 antialiased">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <svg class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                    <span class="ml-2 text-xl font-bold text-gray-900">AgroSystem</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-emerald-600 font-medium transition-colors">Log in</a>
                    <a href="{{ route('register') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32 pt-10 lg:pt-16">
                <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                    <div class="sm:text-center lg:text-left">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                            <span class="block xl:inline">Majukan Pertanian</span>
                            <span class="block text-emerald-600">Era Digital</span>
                        </h1>
                        <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                            Platform terintegrasi untuk memantau harga komoditas (BPS), perdagangan hasil panen, distribusi pupuk subsidi, hingga peta lahan pertanian.
                        </p>
                        <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                            <div class="rounded-md shadow">
                                <a href="{{ route('register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 md:py-4 md:text-lg md:px-10 transition-all">
                                    Mulai Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
            <img class="h-56 w-full object-cover sm:h-72 md:h-96 lg:w-full lg:h-full" src="https://images.unsplash.com/photo-1625246333195-78d9c38ad449?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Pertanian">
            <div class="absolute inset-0 bg-emerald-700/20 mix-blend-multiply"></div>
        </div>
    </div>

    <!-- Commodity Prices Section -->
    <div class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-base text-emerald-600 font-semibold tracking-wide uppercase">Informasi Pasar</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Harga Komoditas Hari Ini
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Data diambil secara berkala dari sumber terpercaya (BPS).
                </p>
            </div>

            <div class="mt-10">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Dummy Data 1 -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-emerald-100 rounded-md p-3">
                                    🌾
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Beras Medium</h3>
                                    <p class="text-sm text-gray-500">Pangan</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-3xl font-bold text-gray-900">Rp 14.500 <span class="text-sm font-medium text-gray-500">/ kg</span></p>
                                <p class="text-sm text-emerald-600 flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                                    +0.5% dari kemarin
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Dummy Data 2 -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                    🌽
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Jagung Kering</h3>
                                    <p class="text-sm text-gray-500">Pangan</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-3xl font-bold text-gray-900">Rp 7.500 <span class="text-sm font-medium text-gray-500">/ kg</span></p>
                                <p class="text-sm text-gray-400 flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                    Stabil
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Dummy Data 3 -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                                    🌶️
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Cabai Rawit</h3>
                                    <p class="text-sm text-gray-500">Hortikultura</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-3xl font-bold text-gray-900">Rp 45.000 <span class="text-sm font-medium text-gray-500">/ kg</span></p>
                                <p class="text-sm text-red-500 flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                    -2.1% dari kemarin
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Dummy Data 4 -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                                    🧅
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Bawang Merah</h3>
                                    <p class="text-sm text-gray-500">Hortikultura</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-3xl font-bold text-gray-900">Rp 32.000 <span class="text-sm font-medium text-gray-500">/ kg</span></p>
                                <p class="text-sm text-emerald-600 flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                                    +1.2% dari kemarin
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Articles Section -->
    <div class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900">Artikel & Berita Terkini</h2>
                    <p class="mt-3 text-xl text-gray-500 sm:mt-4">Edukasi dan informasi seputar dunia pertanian.</p>
                </div>
                <a href="#" class="hidden md:block text-emerald-600 hover:text-emerald-700 font-medium">Lihat Semua Berita &rarr;</a>
            </div>
            
            <div class="mt-10 grid gap-8 lg:grid-cols-3 sm:grid-cols-2">
                <!-- Article 1 -->
                <div class="flex flex-col rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="flex-shrink-0">
                        <img class="h-48 w-full object-cover" src="https://images.unsplash.com/photo-1592982537447-6f23f5c53139?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Traktor">
                    </div>
                    <div class="flex-1 bg-white p-6 flex flex-col justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-emerald-600">Teknologi</p>
                            <a href="#" class="block mt-2">
                                <p class="text-xl font-semibold text-gray-900 hover:text-emerald-600 transition-colors">Penggunaan Traktor Pintar untuk Efisiensi Lahan</p>
                                <p class="mt-3 text-base text-gray-500 line-clamp-3">Mekanisasi pertanian semakin maju dengan hadirnya traktor otonom yang bisa dikendalikan melalui smartphone. Pelajari bagaimana kelompok tani di pulau Jawa mulai mengadopsi teknologi ini.</p>
                            </a>
                        </div>
                        <div class="mt-6 flex items-center">
                            <div class="text-sm">
                                <p class="text-gray-900 font-medium">Budi Santoso</p>
                                <div class="flex space-x-1 text-gray-500">
                                    <time datetime="2026-06-10">10 Juni 2026</time>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Article 2 -->
                <div class="flex flex-col rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="flex-shrink-0">
                        <img class="h-48 w-full object-cover" src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Pupuk">
                    </div>
                    <div class="flex-1 bg-white p-6 flex flex-col justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-emerald-600">Kebijakan</p>
                            <a href="#" class="block mt-2">
                                <p class="text-xl font-semibold text-gray-900 hover:text-emerald-600 transition-colors">Syarat Mendapatkan Pupuk Subsidi 2026</p>
                                <p class="mt-3 text-base text-gray-500 line-clamp-3">Pemerintah telah menetapkan regulasi baru terkait penyaluran pupuk subsidi. Pastikan NIK Anda telah tervalidasi dan tergabung dalam kelompok tani resmi terdaftar.</p>
                            </a>
                        </div>
                        <div class="mt-6 flex items-center">
                            <div class="text-sm">
                                <p class="text-gray-900 font-medium">Kementerian Pertanian</p>
                                <div class="flex space-x-1 text-gray-500">
                                    <time datetime="2026-06-08">8 Juni 2026</time>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Article 3 -->
                <div class="flex flex-col rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="flex-shrink-0">
                        <img class="h-48 w-full object-cover" src="https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Pasar">
                    </div>
                    <div class="flex-1 bg-white p-6 flex flex-col justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-emerald-600">Ekonomi</p>
                            <a href="#" class="block mt-2">
                                <p class="text-xl font-semibold text-gray-900 hover:text-emerald-600 transition-colors">Tips Menjual Hasil Panen Langsung ke Pembeli</p>
                                <p class="mt-3 text-base text-gray-500 line-clamp-3">Platform e-commerce pertanian kini mempermudah petani untuk memotong rantai distribusi. Dapatkan keuntungan lebih besar dengan mengikuti panduan pengemasan standar supermarket.</p>
                            </a>
                        </div>
                        <div class="mt-6 flex items-center">
                            <div class="text-sm">
                                <p class="text-gray-900 font-medium">Siti Rahma</p>
                                <div class="flex space-x-1 text-gray-500">
                                    <time datetime="2026-06-05">5 Juni 2026</time>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center text-white">
                    <svg class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                    <span class="ml-2 text-lg font-semibold">AgroSystem</span>
                </div>
                <p class="text-gray-400 text-sm">&copy; 2026 AgroSystem. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

</body>
</html>
