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
    }
}
