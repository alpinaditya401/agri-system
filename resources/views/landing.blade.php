<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agrilink – Mega-Panel Integrasi Fitur Pertanian</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; scroll-behavior: smooth; }
    /* UI/UX Pro Max Styles */
    body { font-family: 'Outfit', sans-serif; scroll-behavior: smooth; }
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
        }
        .text-gradient {
            background: linear-gradient(135deg, #34d399 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .blob {
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            animation: morph 8s ease-in-out infinite;
        }
        @keyframes morph {
            0%, 100% { border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; }
            34% { border-radius: 70% 30% 50% 50% / 30% 30% 70% 70%; }
            67% { border-radius: 100% 60% 60% 100% / 100% 100% 60% 60%; }
        }
        .float-anim {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased selection:bg-emerald-500 selection:text-white">

  <!-- SVG Filter for Liquid Glass Button -->
  <svg class="hidden">
    <defs>
      <filter id="container-glass" x="0%" y="0%" width="100%" height="100%" color-interpolation-filters="sRGB">
        <feTurbulence type="fractalNoise" baseFrequency="0.05 0.05" numOctaves="1" seed="1" result="turbulence" />
        <feGaussianBlur in="turbulence" stdDeviation="2" result="blurredNoise" />
        <feDisplacementMap in="SourceGraphic" in2="blurredNoise" scale="70" xChannelSelector="R" yChannelSelector="B" result="displaced" />
        <feGaussianBlur in="displaced" stdDeviation="4" result="finalBlur" />
        <feComposite in="finalBlur" in2="finalBlur" operator="over" />
      </filter>
    </defs>
  </svg>

    <!-- NAVBAR -->
    <nav class="fixed w-full z-50 transition-all duration-300 glass-panel border-b border-white/10" id="navbar">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center gap-3 cursor-pointer group">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C8 2 4 6 4 10c0 5 8 12 8 12s8-7 8-12c0-4-4-8-8-8z"/>
                    </svg>
                </div>
                <span class="font-bold text-2xl tracking-tight text-white drop-shadow-md">Agrilink<span class="text-emerald-400">.</span></span>
            </div>

            <!-- Nav Links -->
            <div class="hidden md:flex items-center gap-8 bg-white/10 px-6 py-2 rounded-full border border-white/10 backdrop-blur-md">
                <a href="#" class="text-white/80 hover:text-white hover:font-semibold transition-all text-sm uppercase tracking-wider">Hama</a>
                <a href="#" class="text-white/80 hover:text-white hover:font-semibold transition-all text-sm uppercase tracking-wider">Artikel</a>
                <a href="{{ route('public.prices') }}" class="text-white/80 hover:text-white hover:font-semibold transition-all text-sm uppercase tracking-wider">Info Harga</a>
                <a href="{{ route('public.map') }}" class="text-white/80 hover:text-white hover:font-semibold transition-all text-sm uppercase tracking-wider">Peta</a>
            </div>

            <!-- CTA -->
            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="text-white/90 font-medium hover:text-white transition-colors text-sm">Masuk</a>
                <a href="{{ route('register') }}" class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white px-6 py-2.5 rounded-full font-semibold transition-all shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-0.5 border border-emerald-400/50 text-sm">
                    Daftar Tani
                </a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="bg-gradient-to-br from-emerald-950 via-teal-950 to-emerald-900 relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
        <!-- Falling Pattern Background -->
        <div id="falling-pattern-container" class="absolute inset-0 z-0 pointer-events-none overflow-hidden" style="background-color: transparent; -webkit-mask-image: radial-gradient(ellipse at center, black 20%, transparent 100%); mask-image: radial-gradient(ellipse at center, black 20%, transparent 100%);">
            <div id="falling-pattern-anim" class="absolute inset-0 w-full h-full opacity-60"></div>
            <div class="absolute inset-0" style="backdrop-filter: blur(1em); -webkit-backdrop-filter: blur(1em); background-image: radial-gradient(circle at 50% 50%, transparent 0, transparent 2px, rgba(2, 44, 34, 0.4) 2px); background-size: 8px 8px;"></div>
        </div>

        <!-- Abstract Shapes (Theme Accent) -->
        <div class="absolute top-1/4 left-10 w-72 h-72 bg-emerald-500/10 rounded-full mix-blend-screen filter blur-3xl blob z-0"></div>
        <div class="absolute bottom-1/4 right-10 w-96 h-96 bg-teal-500/10 rounded-full mix-blend-screen filter blur-3xl blob z-0" style="animation-delay: -2s;"></div>

        <div class="max-w-5xl mx-auto px-6 relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 backdrop-blur-md mb-8 float-anim">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-emerald-100 text-xs font-semibold tracking-widest uppercase">Ekosistem Digital Petani 4.0</span>
            </div>
            
            <h1 class="text-5xl md:text-7xl font-extrabold text-white leading-tight mb-6 tracking-tight">
                Masa Depan Pertanian<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 to-teal-300">Berawal di Sini</span>
            </h1>
            
            <p class="text-emerald-50/80 text-lg md:text-2xl max-w-3xl mx-auto mb-12 font-light leading-relaxed">
                Platform terintegrasi dengan teknologi modern untuk memudahkan petani mengelola, memasarkan, dan mengembangkan hasil tani secara cerdas.
            </p>

            <!-- Hero Buttons -->
            <div class="flex flex-col sm:flex-row justify-center gap-5">
                <a href="{{ route('public.prices') }}" class="group relative inline-flex items-center justify-center cursor-pointer gap-2 whitespace-nowrap rounded-full text-lg font-bold transition-all duration-300 hover:scale-105 text-white bg-transparent h-14 px-10 border border-white/20">
                    <div class="absolute top-0 left-0 z-0 h-full w-full rounded-full shadow-[0_0_6px_rgba(0,0,0,0.03),0_2px_6px_rgba(0,0,0,0.08),inset_3px_3px_0.5px_-3px_rgba(0,0,0,0.9),inset_-3px_-3px_0.5px_-3px_rgba(0,0,0,0.85),inset_1px_1px_1px_-0.5px_rgba(0,0,0,0.6),inset_-1px_-1px_1px_-0.5px_rgba(0,0,0,0.6),inset_0_0_6px_6px_rgba(0,0,0,0.12),inset_0_0_2px_2px_rgba(0,0,0,0.06),0_0_12px_rgba(255,255,255,0.15)] transition-all dark:shadow-[0_0_8px_rgba(0,0,0,0.03),0_2px_6px_rgba(0,0,0,0.08),inset_3px_3px_0.5px_-3.5px_rgba(255,255,255,0.09),inset_-3px_-3px_0.5px_-3.5px_rgba(255,255,255,0.85),inset_1px_1px_1px_-0.5px_rgba(255,255,255,0.6),inset_-1px_-1px_1px_-0.5px_rgba(255,255,255,0.6),inset_0_0_6px_6px_rgba(255,255,255,0.12),inset_0_0_2px_2px_rgba(255,255,255,0.06),0_0_12px_rgba(0,0,0,0.15)]"></div>
                    <div class="absolute top-0 left-0 isolate -z-10 h-full w-full overflow-hidden rounded-full" style="backdrop-filter: url('#container-glass');"></div>
                    <div class="pointer-events-none z-10 flex items-center gap-3">
                        <span class="text-xl">🌾</span> 
                        <span>Jelajahi Harga</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </div>
                </a>
                <a href="#pupuk" class="group relative inline-flex items-center justify-center cursor-pointer gap-2 whitespace-nowrap rounded-full text-lg font-bold transition-all duration-300 hover:scale-105 text-emerald-400 bg-transparent h-14 px-10 border border-emerald-500/50 hover:bg-emerald-500/10 hover:border-emerald-400">
                    <div class="absolute top-0 left-0 z-0 h-full w-full rounded-full shadow-[0_0_6px_rgba(0,0,0,0.03),0_2px_6px_rgba(0,0,0,0.08),inset_3px_3px_0.5px_-3px_rgba(0,0,0,0.9),inset_-3px_-3px_0.5px_-3px_rgba(0,0,0,0.85),inset_1px_1px_1px_-0.5px_rgba(0,0,0,0.6),inset_-1px_-1px_1px_-0.5px_rgba(0,0,0,0.6),inset_0_0_6px_6px_rgba(0,0,0,0.12),inset_0_0_2px_2px_rgba(0,0,0,0.06),0_0_12px_rgba(255,255,255,0.15)] transition-all dark:shadow-[0_0_8px_rgba(0,0,0,0.03),0_2px_6px_rgba(0,0,0,0.08),inset_3px_3px_0.5px_-3.5px_rgba(255,255,255,0.09),inset_-3px_-3px_0.5px_-3.5px_rgba(255,255,255,0.85),inset_1px_1px_1px_-0.5px_rgba(255,255,255,0.6),inset_-1px_-1px_1px_-0.5px_rgba(255,255,255,0.6),inset_0_0_6px_6px_rgba(255,255,255,0.12),inset_0_0_2px_2px_rgba(255,255,255,0.06),0_0_12px_rgba(0,0,0,0.15)]"></div>
                    <div class="absolute top-0 left-0 isolate -z-10 h-full w-full overflow-hidden rounded-full" style="backdrop-filter: url('#container-glass');"></div>
                    <div class="pointer-events-none z-10 flex items-center gap-3">
                        <span class="text-xl">🧪</span> 
                        Cek Jatah Pupuk
                    </div>
                </a>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
        </div>
    </section>

    <!-- FLOATING FEATURE CARDS -->
    <section class="relative z-20 -mt-24 px-4 pb-20">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Card 1 -->
            <div class="glass-card rounded-3xl p-8 hover:-translate-y-3 transition-all duration-500 cursor-pointer group">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 text-xl mb-3">Pantau Harga Pasar</h3>
                <p class="text-gray-500 leading-relaxed text-sm">Akses data komoditas real-time langsung dari sumber terpercaya untuk keputusan panen yang lebih menguntungkan.</p>
            </div>

            <!-- Card 2 -->
            <div class="glass-card rounded-3xl p-8 hover:-translate-y-3 transition-all duration-500 cursor-pointer group relative overflow-hidden border-emerald-200 shadow-emerald-900/5">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl -mr-10 -mt-10 transition-all group-hover:bg-emerald-500/20"></div>
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white flex items-center justify-center mb-6 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-500 shadow-md shadow-emerald-500/20 relative z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 text-xl mb-3 relative z-10">Cek Jatah Pupuk</h3>
                <p class="text-gray-500 leading-relaxed text-sm relative z-10">Sistem pintar untuk mengelola dan melacak distribusi pupuk bersubsidi secara transparan dan efisien.</p>
            </div>

            <!-- Card 3 -->
            <div class="glass-card rounded-3xl p-8 hover:-translate-y-3 transition-all duration-500 cursor-pointer group">
                <div class="w-16 h-16 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 text-xl mb-3">Jual Beli Online</h3>
                <p class="text-gray-500 leading-relaxed text-sm">Marketplace terintegrasi yang menghubungkan petani langsung dengan pembeli tanpa perantara.</p>
            </div>
        </div>
    </section>

    <!-- FEATURE GRID SECTION -->
    <section class="py-24 bg-white relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute top-0 right-0 w-1/3 h-full bg-emerald-50/50 rounded-l-full blur-3xl opacity-50 transform translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-1/4 h-1/2 bg-teal-50/50 rounded-r-full blur-3xl opacity-50 transform -translate-x-1/2"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="text-center mb-20">
                <span class="text-emerald-500 font-bold tracking-wider uppercase text-sm mb-3 block">Fitur Unggulan</span>
                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6 tracking-tight">Semua Yang Anda Butuhkan</h2>
                <div class="w-24 h-1 bg-gradient-to-r from-emerald-400 to-teal-500 mx-auto rounded-full mb-6"></div>
                <p class="text-gray-500 text-lg md:text-xl max-w-2xl mx-auto">Sistem terintegrasi dalam 1 platform yang dirancang khusus untuk memajukan kesejahteraan petani.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Feature 1 -->
                <div class="group relative bg-white p-10 rounded-[2.5rem] border border-gray-100 hover:border-emerald-100 hover:shadow-2xl hover:shadow-emerald-900/5 transition-all duration-500">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-[2.5rem]"></div>
                    <div class="relative z-10">
                        <div class="w-20 h-20 rounded-2xl bg-emerald-100 flex items-center justify-center mb-8 group-hover:bg-emerald-500 transition-colors duration-500">
                            <svg class="w-10 h-10 text-emerald-600 group-hover:text-white transition-colors duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-emerald-700 transition-colors">Info Harga Real-time</h3>
                        <p class="text-gray-500 leading-relaxed">Data harga komoditas terkini dari BPS, diperbarui secara otomatis setiap hari untuk analisis pasar yang akurat.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="group relative bg-white p-10 rounded-[2.5rem] border border-gray-100 hover:border-teal-100 hover:shadow-2xl hover:shadow-teal-900/5 transition-all duration-500 transform md:-translate-y-8">
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-[2.5rem]"></div>
                    <div class="relative z-10">
                        <div class="w-20 h-20 rounded-2xl bg-teal-100 flex items-center justify-center mb-8 group-hover:bg-teal-500 transition-colors duration-500">
                            <svg class="w-10 h-10 text-teal-600 group-hover:text-white transition-colors duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-teal-700 transition-colors">Marketplace C2C</h3>
                        <p class="text-gray-500 leading-relaxed">Platform jual beli online yang menghubungkan petani langsung dengan konsumen, memangkas rantai distribusi panjang.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="group relative bg-white p-10 rounded-[2.5rem] border border-gray-100 hover:border-emerald-100 hover:shadow-2xl hover:shadow-emerald-900/5 transition-all duration-500">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-[2.5rem]"></div>
                    <div class="relative z-10">
                        <div class="w-20 h-20 rounded-2xl bg-emerald-100 flex items-center justify-center mb-8 group-hover:bg-emerald-500 transition-colors duration-500">
                            <svg class="w-10 h-10 text-emerald-600 group-hover:text-white transition-colors duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-emerald-700 transition-colors">Distribusi Subsidi</h3>
                        <p class="text-gray-500 leading-relaxed">Manajemen alokasi pupuk bersubsidi dengan validasi NIK yang aman, memastikan distribusi tepat sasaran.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION -->
    <section class="py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-900 to-teal-900"></div>
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1592982537447-6f2c6a0c5950?auto=format&fit=crop&w=1920&q=80')] mix-blend-overlay opacity-20 bg-cover bg-center"></div>
        
        <div class="max-w-5xl mx-auto px-6 relative z-10 text-center">
            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-6">Siap Memajukan Pertanian Anda?</h2>
            <p class="text-emerald-100 text-xl mb-10 max-w-2xl mx-auto">Bergabunglah dengan ribuan petani lainnya yang telah merasakan kemudahan bertani di era digital.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-3 bg-white text-emerald-900 px-10 py-4 rounded-full font-bold text-lg hover:scale-105 hover:shadow-[0_0_40px_rgba(255,255,255,0.4)] transition-all duration-300">
                Mulai Sekarang Gratis
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7-7m7-7H3"></path></svg>
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-gray-950 text-gray-300 py-16 border-t border-gray-900">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <!-- Brand -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C8 2 4 6 4 10c0 5 8 12 8 12s8-7 8-12c0-4-4-8-8-8z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-2xl text-white">Agrilink<span class="text-emerald-400">.</span></span>
                    </div>
                    <p class="text-gray-400 max-w-sm leading-relaxed mb-6">Platform pertanian modern terintegrasi untuk mewujudkan ekosistem digital petani Indonesia yang lebih sejahtera dan mandiri.</p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-900 flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-all"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-900 flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-all"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
                    </div>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="text-white font-bold mb-6">Menu Navigasi</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Beranda</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Tentang Kami</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Artikel Pertanian</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Kontak</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h4 class="text-white font-bold mb-6">Layanan</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Info Harga BPS</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Jatah Pupuk Subsidi</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Marketplace Hasil Tani</a></li>
                        <li><a href="#" class="hover:text-emerald-400 transition-colors">Pemetaan Hama</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-900 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500">&copy; 2026 Agrilink. Tugas Akhir Pengamanan Web.</p>
                <div class="flex gap-6 text-sm">
                    <a href="#" class="text-gray-500 hover:text-emerald-400 transition-colors">Syarat & Ketentuan</a>
                    <a href="#" class="text-gray-500 hover:text-emerald-400 transition-colors">Kebijakan Privasi</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Simple Navbar Scroll Effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) {
                nav.classList.add('bg-emerald-950/90', 'backdrop-blur-xl', 'shadow-lg');
                nav.classList.remove('glass-panel');
            } else {
                nav.classList.remove('bg-emerald-950/90', 'backdrop-blur-xl', 'shadow-lg');
                nav.classList.add('glass-panel');
            }
        });
    </script>

    <!-- Falling Pattern Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Falling pattern colors adapted for Agrilink Theme (Emerald)
            const color = 'rgba(16, 185, 129, 0.8)'; // emerald-500 with opacity
            
            const patterns = [
                `radial-gradient(4px 100px at 0px 235px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 235px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 117.5px, ${color} 100%, transparent 150%)`,
                `radial-gradient(4px 100px at 0px 252px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 252px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 126px, ${color} 100%, transparent 150%)`,
                `radial-gradient(4px 100px at 0px 150px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 150px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 75px, ${color} 100%, transparent 150%)`,
                `radial-gradient(4px 100px at 0px 253px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 253px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 126.5px, ${color} 100%, transparent 150%)`,
                `radial-gradient(4px 100px at 0px 204px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 204px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 102px, ${color} 100%, transparent 150%)`,
                `radial-gradient(4px 100px at 0px 134px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 134px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 67px, ${color} 100%, transparent 150%)`,
                `radial-gradient(4px 100px at 0px 179px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 179px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 89.5px, ${color} 100%, transparent 150%)`,
                `radial-gradient(4px 100px at 0px 299px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 299px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 149.5px, ${color} 100%, transparent 150%)`,
                `radial-gradient(4px 100px at 0px 215px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 215px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 107.5px, ${color} 100%, transparent 150%)`,
                `radial-gradient(4px 100px at 0px 281px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 281px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 140.5px, ${color} 100%, transparent 150%)`,
                `radial-gradient(4px 100px at 0px 158px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 158px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 79px, ${color} 100%, transparent 150%)`,
                `radial-gradient(4px 100px at 0px 210px, ${color}, transparent)`,
                `radial-gradient(4px 100px at 300px 210px, ${color}, transparent)`,
                `radial-gradient(1.5px 1.5px at 150px 105px, ${color} 100%, transparent 150%)`
            ];

            const backgroundSizes = [
                '300px 235px', '300px 235px', '300px 235px', '300px 252px', '300px 252px', '300px 252px',
                '300px 150px', '300px 150px', '300px 150px', '300px 253px', '300px 253px', '300px 253px',
                '300px 204px', '300px 204px', '300px 204px', '300px 134px', '300px 134px', '300px 134px',
                '300px 179px', '300px 179px', '300px 179px', '300px 299px', '300px 299px', '300px 299px',
                '300px 215px', '300px 215px', '300px 215px', '300px 281px', '300px 281px', '300px 281px',
                '300px 158px', '300px 158px', '300px 158px', '300px 210px', '300px 210px', '300px 210px'
            ];

            const startPositions = '0px 220px, 3px 220px, 151.5px 337.5px, 25px 24px, 28px 24px, 176.5px 150px, 50px 16px, 53px 16px, 201.5px 91px, 75px 224px, 78px 224px, 226.5px 230.5px, 100px 19px, 103px 19px, 251.5px 121px, 125px 120px, 128px 120px, 276.5px 187px, 150px 31px, 153px 31px, 301.5px 120.5px, 175px 235px, 178px 235px, 326.5px 384.5px, 200px 121px, 203px 121px, 351.5px 228.5px, 225px 224px, 228px 224px, 376.5px 364.5px, 250px 26px, 253px 26px, 401.5px 105px, 275px 75px, 278px 75px, 426.5px 180px';
            const endPositions = '0px 6800px, 3px 6800px, 151.5px 6917.5px, 25px 13632px, 28px 13632px, 176.5px 13758px, 50px 5416px, 53px 5416px, 201.5px 5491px, 75px 17175px, 78px 17175px, 226.5px 17301.5px, 100px 5119px, 103px 5119px, 251.5px 5221px, 125px 8428px, 128px 8428px, 276.5px 8495px, 150px 9876px, 153px 9876px, 301.5px 9965.5px, 175px 13391px, 178px 13391px, 326.5px 13540.5px, 200px 14741px, 203px 14741px, 351.5px 14848.5px, 225px 18770px, 228px 18770px, 376.5px 18910.5px, 250px 5082px, 253px 5082px, 401.5px 5161px, 275px 6375px, 278px 6375px, 426.5px 6480px';

            const animEl = document.getElementById('falling-pattern-anim');
            if(animEl) {
                animEl.style.backgroundImage = patterns.join(', ');
                animEl.style.backgroundSize = backgroundSizes.join(', ');
                
                // Simple polyfill to fade in the animation block
                animEl.style.opacity = '0';
                setTimeout(() => { animEl.style.transition = 'opacity 0.5s ease'; animEl.style.opacity = '0.6'; }, 100);

                animEl.animate([
                    { backgroundPosition: startPositions },
                    { backgroundPosition: endPositions }
                ], {
                    duration: 150000,
                    iterations: Infinity,
                    easing: 'linear'
                });
            }
        });
    </script>
</body>
</html>
