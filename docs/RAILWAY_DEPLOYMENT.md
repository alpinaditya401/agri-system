# Railway Deployment Agrilink

Dokumen ini merangkum hal yang perlu dilakukan saat deploy Agrilink ke Railway.

## Web Service

Web service utama tetap menjalankan aplikasi Laravel. Jangan jadikan web service utama sebagai scheduler.

Checklist command deploy umum:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link || true
php artisan optimize
```

## Scheduler / Cron Job

Laravel scheduler tidak otomatis berjalan di Railway. Buat service atau cron job terpisah:

- Cron Schedule: `* * * * *`
- Start Command: `php artisan schedule:run`
- Service harus exit setelah command selesai.

Command yang dijadwalkan di `routes/console.php`:

- `bps:fetch-prices` harian pukul `00:00`
- `fertilizer:quota-reminder` harian pukul `07:00`

Cek dari shell Railway:

```bash
php artisan schedule:list
```

## Storage Upload

Foto profil dan gambar produk membutuhkan symbolic link public storage:

```bash
php artisan storage:link
```

Jika Railway memakai filesystem ephemeral, upload bisa hilang saat redeploy. Gunakan Railway Volume atau storage eksternal jika upload harus persistent.

## Seeder Production

Seeder rich dashboard dan artikel edukasi tidak otomatis membersihkan data asli. Jalankan manual jika perlu:

```bash
php artisan db:seed --class=RichDashboardDataSeeder --force
php artisan db:seed --class=BpsSourceArticleSeeder --force
```

## Produk Duplikat

Audit dulu sebelum membersihkan data production:

```bash
php artisan products:deduplicate
```

Setelah backup database dan hasil dry-run benar:

```bash
php artisan products:deduplicate --apply
```
