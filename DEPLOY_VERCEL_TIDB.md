# Deploy Agrilink ke Vercel + TiDB Cloud

Project utama sekarang ada di:

```text
C:\xampp\htdocs\agri-system
```

Folder lama tidak dihapus. Backup-nya ada di:

```text
C:\xampp\htdocs\agri-system_legacy_before_cleanup_20260627-120337
```

## 1. Catatan Penting

Vercel resmi menyediakan runtime Node.js, Bun, Python, Rust, Go, Ruby, Wasm, dan Edge. PHP tidak termasuk runtime resmi, jadi project Laravel ini memakai community runtime `vercel-php` lewat `vercel.json`.

Vercel Functions juga memakai filesystem read-only, hanya `/tmp` yang writable. Jadi database tidak boleh SQLite lokal di Vercel, dan upload gambar/file tidak aman disimpan ke folder lokal. Untuk database pakai TiDB Cloud. Untuk upload file production, pakai Cloudinary/S3-compatible storage.

Referensi:

- Vercel runtimes: https://vercel.com/docs/functions/runtimes
- PHP community runtime: https://github.com/vercel-community/php
- TiDB import SQL via MySQL CLI: https://docs.pingcap.com/tidbcloud/import-with-mysql-cli-serverless/

## 2. Upload Database ke TiDB Cloud

File SQL sudah tersedia di:

```text
database\exports\agri_system_tidb_full.sql
```

Cara paling gampang:

1. Login ke TiDB Cloud.
2. Buat cluster Starter/Essential.
3. Buka cluster, klik `Connect`.
4. Copy host, port, username, dan password.
5. Buat database:

```sql
CREATE DATABASE agri_system;
```

6. Import SQL dari komputer:

```bash
mysql --comments --connect-timeout 150 ^
  -u "USERNAME_TIDB" ^
  -h "HOST_TIDB" ^
  -P 4000 ^
  -D agri_system ^
  --ssl-mode=VERIFY_IDENTITY ^
  -p < database/exports/agri_system_tidb_full.sql
```

Di Windows PowerShell, kalau command multiline menyulitkan, pakai satu baris:

```bash
mysql --comments --connect-timeout 150 -u "USERNAME_TIDB" -h "HOST_TIDB" -P 4000 -D agri_system --ssl-mode=VERIFY_IDENTITY -p < database/exports/agri_system_tidb_full.sql
```

Kalau TiDB memberi CA certificate path, tambahkan:

```bash
--ssl-ca="PATH_CA_CERT"
```

## 3. Siapkan Environment Variables di Vercel

Di Vercel Project Settings > Environment Variables, isi:

```env
APP_NAME=Agrilink
APP_ENV=production
APP_KEY=base64:ISI_DARI_php_artisan_key_generate_show
APP_DEBUG=false
APP_URL=https://nama-project.vercel.app

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=HOST_TIDB
DB_PORT=4000
DB_DATABASE=agri_system
DB_USERNAME=USERNAME_TIDB
DB_PASSWORD=PASSWORD_TIDB

BPS_API_BASE_URL=https://webapi.bps.go.id/v1/api
BPS_API_DOMAIN=0000
BPS_API_KEY=ISI_API_KEY_BPS
BPS_API_TIMEOUT=30

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
```

Buat `APP_KEY` dengan:

```bash
php artisan key:generate --show
```

## 4. Deploy ke Vercel

Cara lewat GitHub:

1. Push project `C:\xampp\htdocs\agri-system` ke GitHub.
2. Login Vercel.
3. `Add New Project`.
4. Import repo GitHub.
5. Pastikan Vercel membaca `vercel.json`.
6. Isi Environment Variables.
7. Klik Deploy.

Cara lewat CLI:

```bash
npm install -g vercel
vercel login
vercel
vercel --prod
```

## 5. Setelah Deploy

1. Buka URL Vercel.
2. Cek halaman login/register.
3. Cek dashboard per role.
4. Kalau error database, cek ulang `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, dan aturan koneksi TiDB.
5. Kalau error asset CSS/JS, cek hasil `npm run build` dan folder `public/build`.

## 6. Generate Ulang SQL Kalau Data SQLite Berubah

```bash
php scripts/export_sqlite_to_tidb.php
```

Lalu import ulang:

```text
database\exports\agri_system_tidb_full.sql
```
