<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class BpsSourceArticleSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::whereHas('role', fn($query) => $query->where('name', 'admin'))->first()
            ?? User::first();

        if (! $author) {
            return;
        }

        Article::updateOrCreate(
            ['slug' => 'sumber-data-bps-agrilink'],
            [
                'author_id' => $author->id,
                'title' => 'Sumber Data BPS Agrilink',
                'excerpt' => 'Daftar tabel statistik BPS yang dipakai Agrilink sebagai rujukan harga komoditas.',
                'content' => implode("\n\n", [
                    'Agrilink memakai Badan Pusat Statistik (BPS) sebagai rujukan data harga komoditas. Untuk konteks publik, sumber yang ditampilkan adalah halaman Tabel Statistik BPS utuh, bukan endpoint API per potongan data.',
                    'Data gabah dan beras berasal dari tabel harga langsung BPS. Data sayur dan buah memakai estimasi harga implisit dari tabel BPS utuh: pengeluaran dibagi konsumsi.',
                    'Daftar link sumber BPS tersedia di bawah artikel ini supaya pembaca bisa membuka konteks data lengkap dari website BPS.',
                ]),
                'cover_image' => null,
                'category' => 'harga',
                'tags' => ['bps', 'harga', 'sumber-data'],
                'status' => 'published',
                'published_at' => now(),
            ]
        );

        $articles = [
            [
                'slug' => 'cara-membaca-harga-komoditas-bps',
                'title' => 'Cara Membaca Harga Komoditas BPS',
                'excerpt' => 'Panduan singkat memahami sumber, kategori, wilayah, dan tanggal harga komoditas yang tampil di Agrilink.',
                'category' => 'harga',
                'tags' => ['bps', 'harga', 'komoditas'],
                'content' => [
                    'Harga komoditas perlu dibaca bersama konteks sumber, wilayah, dan tanggal data. Angka terbaru belum tentu mewakili harga transaksi di semua pasar lokal, tetapi bisa menjadi patokan awal untuk negosiasi.',
                    'Di Agrilink, data harga dipakai sebagai referensi pembeli dan petani saat membandingkan harga produk. Perhatikan kategori, satuan, dan wilayah sebelum mengambil keputusan.',
                    'Jika harga produk jauh di atas atau di bawah referensi, cek kualitas barang, jarak distribusi, dan biaya pengemasan agar keputusan tetap adil untuk semua pihak.',
                ],
            ],
            [
                'slug' => 'panduan-menjual-produk-tani-di-agrilink',
                'title' => 'Panduan Menjual Produk Tani di Agrilink',
                'excerpt' => 'Langkah praktis untuk petani menyiapkan produk, stok, harga, dan deskripsi yang mudah dipercaya pembeli.',
                'category' => 'marketplace',
                'tags' => ['produk', 'petani', 'marketplace'],
                'content' => [
                    'Produk tani yang baik perlu informasi jelas: nama komoditas, satuan, stok tersedia, minimal order, asal wilayah, dan foto yang sesuai kondisi barang.',
                    'Gunakan harga yang realistis dengan membandingkan referensi harga komoditas. Jelaskan keunggulan produk seperti panen baru, varietas, kualitas sortir, atau metode budidaya.',
                    'Pastikan stok diperbarui setelah transaksi agar pembeli tidak checkout barang yang sudah habis.',
                ],
            ],
            [
                'slug' => 'panduan-mengajukan-pupuk-bersubsidi',
                'title' => 'Panduan Mengajukan Pupuk Bersubsidi',
                'excerpt' => 'Apa saja yang perlu dilengkapi petani sebelum mengajukan kuota dan transaksi pupuk bersubsidi.',
                'category' => 'pupuk',
                'tags' => ['pupuk', 'subsidi', 'kuota'],
                'content' => [
                    'Sebelum mengajukan pupuk bersubsidi, pastikan profil petani sudah lengkap dan terverifikasi. NIK, kelompok tani, luas lahan, dan komoditas utama membantu admin mengalokasikan kuota.',
                    'Kuota pupuk dihitung per jenis pupuk, musim tanam, dan tahun berjalan. Jika kuota belum muncul, hubungi admin atau lengkapi data profil terlebih dahulu.',
                    'Setelah pengajuan dibuat, distributor akan memproses permintaan sesuai stok dan aturan kuota yang tersedia.',
                ],
            ],
            [
                'slug' => 'tips-menjaga-kualitas-gabah-dan-beras',
                'title' => 'Tips Menjaga Kualitas Gabah dan Beras',
                'excerpt' => 'Cara sederhana menjaga kadar air, kebersihan, dan penyimpanan agar gabah dan beras tetap bernilai baik.',
                'category' => 'edukasi',
                'tags' => ['gabah', 'beras', 'kualitas'],
                'content' => [
                    'Kualitas gabah dan beras sangat dipengaruhi proses panen, pengeringan, dan penyimpanan. Kadar air yang terlalu tinggi dapat menurunkan harga dan memperbesar risiko jamur.',
                    'Gunakan alas bersih saat pengeringan, hindari pencampuran dengan kotoran, dan simpan hasil panen di ruang kering dengan sirkulasi baik.',
                    'Saat menjual melalui marketplace, jelaskan kondisi produk secara jujur agar pembeli bisa menilai harga dengan tepat.',
                ],
            ],
            [
                'slug' => 'distribusi-pertanian-digital-untuk-petani-lokal',
                'title' => 'Distribusi Pertanian Digital untuk Petani Lokal',
                'excerpt' => 'Mengapa data lokasi, stok, dan status pengiriman penting untuk mempercepat distribusi hasil tani dan pupuk.',
                'category' => 'distribusi',
                'tags' => ['distribusi', 'peta', 'digital'],
                'content' => [
                    'Distribusi pertanian digital membantu petani, pembeli, dan distributor melihat posisi pasokan secara lebih transparan. Data lokasi membuat pencarian produk dan stok pupuk lebih efisien.',
                    'Dengan status transaksi dan notifikasi, setiap pihak bisa mengetahui kapan pesanan diproses, dikirim, atau selesai.',
                    'Peta distribusi di Agrilink dirancang sebagai ringkasan visual agar keputusan operasional lebih cepat dan mudah dipahami.',
                ],
            ],
        ];

        foreach ($articles as $article) {
            Article::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'author_id' => $author->id,
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content' => implode("\n\n", $article['content']),
                    'cover_image' => null,
                    'category' => $article['category'],
                    'tags' => $article['tags'],
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );
        }
    }
}
