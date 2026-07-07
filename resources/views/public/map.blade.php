<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Distribusi - Agrilink</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
    <x-public-nav active="map" />

    <main class="pt-20">
        <section class="bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-900 text-white">
            <div class="ag-container py-14">
                <p class="text-xs font-bold uppercase text-emerald-200 tracking-[0.24em]">Sebaran Wilayah</p>
                <h1 class="mt-4 text-4xl font-black leading-none md:text-6xl">Peta Distribusi</h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-emerald-50/78">
                    Lihat sebaran petani, distributor, dan produk aktif di wilayah pertanian.
                </p>
            </div>
        </section>

        <section class="ag-container py-8">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-3 shadow-xl shadow-slate-900/5 md:p-5">
                <x-leaflet-map height="580px" :live-track="true" />
            </div>

            <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ([
                    ['title' => 'Petani', 'desc' => 'Titik hijau menunjukkan lokasi petani terverifikasi beserta komoditas dan produk aktif.', 'wrap' => 'bg-emerald-50', 'dot' => 'bg-emerald-500'],
                    ['title' => 'Distributor', 'desc' => 'Titik amber menandai distributor resmi dan ketersediaan stok pupuk bersubsidi.', 'wrap' => 'bg-amber-50', 'dot' => 'bg-amber-500'],
                    ['title' => 'Produk', 'desc' => 'Titik biru menampilkan produk aktif lengkap dengan harga, stok, dan tautan detail.', 'wrap' => 'bg-sky-50', 'dot' => 'bg-sky-500'],
                ] as $item)
                    <div class="ag-card p-5">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl {{ $item['wrap'] }}">
                                <span class="h-3 w-3 rounded-full {{ $item['dot'] }}"></span>
                            </span>
                            <p class="font-black text-slate-950">{{ $item['title'] }}</p>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-slate-500">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs font-semibold text-slate-400">
        Agrilink - Sistem pertanian digital untuk distribusi, niaga, dan informasi harga.
    </footer>
</body>
</html>
