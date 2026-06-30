<?php

return [
    'tables' => [
        'gabah' => [
            'label' => 'Rata-rata Harga Gabah Bulanan Menurut Kualitas, Komponen Mutu dan HPP di Tingkat Petani',
            'short_label' => 'Tabel Harga Gabah',
            'url' => 'https://www.bps.go.id/id/statistics-table/2/MTAzNCMy/rata-rata-harga-gabah-bulanan-menurut-kualitas-komponen-mutu-dan-hpp-di-tingkat-petani.html',
            'description' => 'Satu tabel BPS utuh untuk GKP, GKG, kualitas gabah, komponen mutu, dan HPP tingkat petani.',
        ],
        'beras' => [
            'label' => 'Rata-rata Harga Beras Bulanan di Tingkat Penggilingan Menurut Kualitas',
            'short_label' => 'Tabel Harga Beras',
            'url' => 'https://www.bps.go.id/id/statistics-table/2/MjI3NyMy/rata-rata-harga-beras-bulanan-di-tingkat-penggilingan-menurut-kualitas-.html',
            'description' => 'Satu tabel BPS utuh untuk harga beras kualitas premium dan medium di tingkat penggilingan.',
        ],
        'sayur_konsumsi' => [
            'label' => 'Rata-rata Konsumsi Perkapita Seminggu Menurut Kelompok Sayur-Sayuran Per Kabupaten/Kota',
            'short_label' => 'Tabel Konsumsi Sayur',
            'url' => 'https://www.bps.go.id/id/statistics-table/2/MjEwMCMy/ratarata-konsumsi-perkapita-seminggumenurut-kelompok-sayur-sayuranper-kabupaten-kota.html',
            'description' => 'Tabel BPS utuh untuk konsumsi sayur-sayuran. Dipakai bersama tabel pengeluaran untuk estimasi harga implisit.',
        ],
        'sayur_pengeluaran' => [
            'label' => 'Rata-rata Pengeluaran Perkapita Seminggu Menurut Kelompok Sayur-Sayuran Per Kabupaten/Kota',
            'short_label' => 'Tabel Pengeluaran Sayur',
            'url' => 'https://www.bps.go.id/id/statistics-table/2/MjExNiMy/rata-rata-pengeluaran-perkapita-seminggu--menurut-kelompok-sayur-sayuran-per-kabupaten-kota.html',
            'description' => 'Tabel BPS utuh untuk pengeluaran sayur-sayuran. Dipakai bersama tabel konsumsi untuk estimasi harga implisit.',
        ],
        'buah_konsumsi' => [
            'label' => 'Rata-rata Konsumsi Perkapita Seminggu Menurut Kelompok Buah-Buahan Per Kabupaten/Kota',
            'short_label' => 'Tabel Konsumsi Buah',
            'url' => 'https://www.bps.go.id/id/statistics-table/2/MjEwMiMy/rata-rata-konsumsi-perkapita-seminggu-menurut-kelompok-buah-buahan-per-kabupaten-kota.html',
            'description' => 'Tabel BPS utuh untuk konsumsi buah-buahan. Dipakai bersama tabel pengeluaran untuk estimasi harga implisit.',
        ],
        'buah_pengeluaran' => [
            'label' => 'Rata-rata Pengeluaran Perkapita Seminggu Menurut Kelompok Buah-Buahan Per Kabupaten/Kota',
            'short_label' => 'Tabel Pengeluaran Buah',
            'url' => 'https://www.bps.go.id/id/statistics-table/2/MjExOCMy/rata-rata-pengeluaran-perkapita-seminggu-menurut-kelompok-buah-buahan-per-kabupaten-kota.html',
            'description' => 'Tabel BPS utuh untuk pengeluaran buah-buahan. Dipakai bersama tabel konsumsi untuk estimasi harga implisit.',
        ],
    ],

    'commodities' => [
        'bps-gabah-gkp-petani' => ['gabah'],
        'bps-gabah-gkg-petani' => ['gabah'],
        'bps-beras-premium-penggilingan' => ['beras'],
        'bps-beras-medium-penggilingan' => ['beras'],
        'bps-sayur-susenas' => ['sayur_konsumsi', 'sayur_pengeluaran'],
        'bps-buah-susenas' => ['buah_konsumsi', 'buah_pengeluaran'],
    ],
];
