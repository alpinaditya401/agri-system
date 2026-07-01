# Agrilink / agri-system

Agrilink adalah platform pertanian digital Indonesia untuk marketplace hasil tani, informasi harga komoditas, distribusi pupuk subsidi, peta distribusi, chat, notifikasi, dan dashboard multi-role.

## Stack

- Laravel 12
- Blade
- Tailwind CSS via Vite
- Chart.js
- Leaflet
- MySQL/TiDB untuk production
- Laravel Sanctum untuk API token

## Fitur Utama

- Harga komoditas berbasis cache BPS dan data demo dashboard.
- Marketplace hasil tani untuk pembeli dan petani.
- Pengajuan dan distribusi pupuk bersubsidi.
- Peta distribusi petani, produk, dan distributor.
- Chat antar pengguna.
- Notifikasi pengiriman, harga, stok, chat, dan status order.
- Dashboard buyer, farmer, distributor, admin, dan admin master.
- Upload foto profil dan gambar produk melalui storage publik.

## Setup Lokal

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run dev
php artisan serve
```

Untuk build production lokal:

```bash
npm run build
```

## Environment Penting

```env
APP_NAME=Agrilink
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-production-anda

DB_CONNECTION=mysql
DB_HOST=host-database
DB_PORT=4000
DB_DATABASE=agri_system
DB_USERNAME=username
DB_PASSWORD=password

BPS_API_KEY=
BPS_BASE_URL=https://webapi.bps.go.id/v1/api
BPS_DOMAIN=0000

PAYMENT_GATEWAY=demo
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
```

Gunakan `PAYMENT_GATEWAY=demo` untuk mode demo auto-paid. Isi Midtrans hanya jika gateway asli sudah siap.

## Akun Demo

Seeder demo memakai password:

```text
password
```

Contoh akun:

- `buyer.demo@agri.com`
- `petani.karawang@agri.com`
- `distributor.demo@agri.com`
- `admin@agri.com`
- `admin.master@agri.com`

## Seeder Data Dashboard

Untuk mengisi dashboard dengan data demo yang lebih lengkap:

```bash
php artisan db:seed --class=RichDashboardDataSeeder --force
php artisan db:seed --class=BpsSourceArticleSeeder --force
```

Di Railway CLI:

```bash
railway run php artisan db:seed --class=RichDashboardDataSeeder --force
railway run php artisan db:seed --class=BpsSourceArticleSeeder --force
```

## Scheduler

Command yang dijadwalkan:

- `bps:fetch-prices` setiap hari pukul `00:00`
- `fertilizer:quota-reminder` setiap hari pukul `07:00`

Cek schedule:

```bash
php artisan schedule:list
```

### Railway Scheduler

Railway tidak otomatis menjalankan Laravel scheduler dari web service utama. Buat service/cron job terpisah di Railway:

- Cron Schedule: `* * * * *`
- Start Command: `php artisan schedule:run`
- Service harus exit setelah command selesai.

Jangan ganti start command web service utama menjadi scheduler, karena web service tetap harus menjalankan aplikasi.

## Deployment Railway

Checklist production:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link || true
php artisan optimize
```

Setelah deploy pertama, jalankan seeder demo jika dibutuhkan:

```bash
php artisan db:seed --class=RichDashboardDataSeeder --force
php artisan db:seed --class=BpsSourceArticleSeeder --force
```

## Storage Upload

Upload foto profil dan gambar produk disimpan di disk `public`. Pastikan symbolic link tersedia:

```bash
php artisan storage:link
```

Jika gambar upload tidak tampil di production, cek:

- `APP_URL` sudah benar.
- `public/storage` sudah dibuat oleh `storage:link`.
- Railway volume/persistent storage sudah disiapkan jika file upload harus bertahan antar deploy.

## Produk Duplikat Production

Jangan hapus data production otomatis. Deteksi dulu:

```bash
php artisan products:deduplicate
```

Jika hasil dry-run sudah benar dan database sudah dibackup:

```bash
php artisan products:deduplicate --apply
```

SQL audit manual:

```sql
SELECT name, farmer_id, category_id, price_per_unit, COUNT(*) AS total
FROM products
WHERE deleted_at IS NULL
GROUP BY name, farmer_id, category_id, price_per_unit
HAVING COUNT(*) > 1;
```

## Testing

```bash
php artisan route:list
php artisan schedule:list
php artisan test
npm run build
```
