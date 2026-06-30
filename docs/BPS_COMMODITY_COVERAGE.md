# Coverage Data BPS Agrilink

Konfigurasi:

- Domain: `0000` (Nasional/Indonesia)
- Sinkron internal: `https://webapi.bps.go.id/v1/api`
- Link publik: halaman web `https://www.bps.go.id/id/statistics-table/...`
- Periode maksimal: data 5 tahun terakhir jika tersedia.

## Tersedia dan Disimpan ke `commodity_prices`

| Komoditas | Kode Internal | Sumber BPS | Keterangan |
| --- | --- | --- | --- |
| Gabah Kering Panen (GKP) | `bps-gabah-gkp-petani` | [Tabel Harga Gabah](https://www.bps.go.id/id/statistics-table/2/MTAzNCMy/rata-rata-harga-gabah-bulanan-menurut-kualitas-komponen-mutu-dan-hpp-di-tingkat-petani.html) | Harga rupiah/kg tingkat petani. |
| Gabah Kering Giling (GKG) | `bps-gabah-gkg-petani` | [Tabel Harga Gabah](https://www.bps.go.id/id/statistics-table/2/MTAzNCMy/rata-rata-harga-gabah-bulanan-menurut-kualitas-komponen-mutu-dan-hpp-di-tingkat-petani.html) | Harga rupiah/kg tingkat petani. |
| Beras Premium | `bps-beras-premium-penggilingan` | [Tabel Harga Beras](https://www.bps.go.id/id/statistics-table/2/MjI3NyMy/rata-rata-harga-beras-bulanan-di-tingkat-penggilingan-menurut-kualitas-.html) | Harga rupiah/kg tingkat penggilingan. |
| Beras Medium | `bps-beras-medium-penggilingan` | [Tabel Harga Beras](https://www.bps.go.id/id/statistics-table/2/MjI3NyMy/rata-rata-harga-beras-bulanan-di-tingkat-penggilingan-menurut-kualitas-.html) | Harga rupiah/kg tingkat penggilingan. |
| Sayur-sayuran | `bps-sayur-susenas` | [Tabel Konsumsi Sayur](https://www.bps.go.id/id/statistics-table/2/MjEwMCMy/ratarata-konsumsi-perkapita-seminggumenurut-kelompok-sayur-sayuranper-kabupaten-kota.html) dan [Tabel Pengeluaran Sayur](https://www.bps.go.id/id/statistics-table/2/MjExNiMy/rata-rata-pengeluaran-perkapita-seminggu--menurut-kelompok-sayur-sayuran-per-kabupaten-kota.html) | Estimasi harga implisit rupiah/kg dari pengeluaran/konsumsi Susenas. |
| Buah-buahan | `bps-buah-susenas` | [Tabel Konsumsi Buah](https://www.bps.go.id/id/statistics-table/2/MjEwMiMy/rata-rata-konsumsi-perkapita-seminggu-menurut-kelompok-buah-buahan-per-kabupaten-kota.html) dan [Tabel Pengeluaran Buah](https://www.bps.go.id/id/statistics-table/2/MjExOCMy/rata-rata-pengeluaran-perkapita-seminggu-menurut-kelompok-buah-buahan-per-kabupaten-kota.html) | Estimasi harga implisit rupiah/kg dari pengeluaran/konsumsi Susenas. |

Catatan: link publik sengaja memakai tabel web BPS yang utuh. WebAPI tetap dipakai aplikasi hanya untuk sinkronisasi otomatis dan cache harga.

## Belum Tersedia sebagai Harga Rupiah Nasional Terbaru dari BPS WebAPI

Komoditas berikut belum ditemukan sebagai harga rupiah nasional <= 5 tahun terakhir di BPS WebAPI domain `0000`:

- Jagung
- Kedelai
- Cabai rawit/cabai merah
- Bawang merah

Catatan hasil research:

- Keyword `harga eceran` menemukan WebAPI `var=254` untuk beberapa barang termasuk cabai, tetapi data terakhirnya 2016 sehingga tidak dipakai karena terlalu lama.
- Cabai dan bawang tersedia di beberapa metadata lama/struktur ongkos usaha, tetapi bukan harga rupiah/kg nasional terbaru.
- Jagung dan kedelai muncul di data produksi atau margin perdagangan, tetapi bukan harga rupiah/kg nasional terbaru.

## Distribusi Pupuk

Sesuai keputusan project, distribusi pupuk memakai data operasional aplikasi:

- `fertilizer_stocks`
- `fertilizer_quotas`
- `fertilizer_transactions`

Data ini lebih nyambung ke dashboard distributor/admin karena berisi stok, reservasi, permintaan, persetujuan, dan penyerahan pupuk di sistem Agrilink.

## Cara Sinkron Manual

```bash
php artisan bps:fetch-prices
```

Untuk test tanpa menyimpan:

```bash
php artisan bps:fetch-prices --dry-run
```
