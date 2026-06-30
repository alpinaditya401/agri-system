# Codex Context Summary - Agrilink

Terakhir diperbarui: 2026-06-28, Asia/Jakarta.

Dokumen ini dibuat supaya sesi Codex berikutnya bisa langsung paham konteks kerja tanpa mengulang eksplorasi dari awal.

## Project dan Lingkungan

- Project utama: `C:\xampp\htdocs\agri-system`
- Project referensi/mirror yang juga sempat diedit: `C:\xampp\htdocs\agri-system-Dewa`
- Framework: Laravel 12, PHP 8.2.
- Server yang pernah dipakai:
  - `http://127.0.0.1:8000`
  - `http://localhost`
- Karena beberapa kali ada indikasi server lokal bisa membaca project berbeda, perubahan penting sering dimirror ke `agri-system` dan `agri-system-Dewa`.
- HTTP test terakhir di `127.0.0.1:8000` terlihat memakai database project utama, karena Admin Master id `11` aktif, sedangkan di Dewa id `13`.

## Preferensi User

- Bahasa kerja: Indonesia, santai, langsung eksekusi.
- User ingin desain memakai pendekatan `ui-ux-pro-max`.
- Login dan register harus berupa form tengah, bukan split kanan/kiri.
- Desain harus clean, balance, tidak monoton hijau gelap.
- Dashboard dan produk perlu ikon berbeda untuk tiap item, tidak boleh semua sama.
- Peta hanya untuk Indonesia.
- Data BPS harus dipakai sebagai satu link data utuh, bukan dipisah-pisah per komponen.
- Saat membuat perubahan besar, buat ringkasan agar Codex berikutnya mengerti.

## Request Awal Sampai Terakhir

1. User meminta link BPS lengkap, contoh format:
   `https://www.bps.go.id/id/statistics-table/2/MjQjMg==/indeks-harga-perdagangan-besar-indonesia.html`
2. User ingin setiap data BPS diperlakukan sebagai satu data utuh.
3. User meminta artikel/link BPS juga ditambahkan.
4. User memberi API key. Jangan commit atau tulis ulang key mentah itu ke file dokumentasi/log tambahan.
5. User melaporkan error:
   - File: `resources\views\public\prices.blade.php`
   - Line: 88
   - Error: `Undefined variable $note`
   - URL: `http://127.0.0.1:8000/harga-komoditas`
6. User meminta peta hanya Indonesia.
7. User meminta UI produk didesain ulang.
8. User mengirim screenshot produk dan meminta warna bagian "Ringkasan Pencarian" lebih balance.
9. User meminta login dan register dibuat lebih bagus, form di tengah, mencontoh folder `C:\xampp\htdocs\agri-system-Dewa`.
10. User meminta dashboard disesuaikan, ikon setiap menu/item dibedakan.
11. User meminta akun demo tampil di login:
    - Buyer
    - Petani
    - Distributor
    - Admin
    - Admin Master
12. User meminta Admin Master dibedakan dari Admin biasa:
    - Ada dashboard baru untuk Admin Master.
    - Isi dashboard mirip Admin biasa.
    - Admin Master bisa kontrol Admin biasa.
    - Admin biasa tidak bisa kontrol Admin Master.
    - Admin Master bisa mengatur role semua user.
13. Request terakhir: buat rangkuman `.md` dari awal hingga akhir supaya Codex berikutnya mengerti.

## Pekerjaan Yang Sudah Dikerjakan

### BPS, Harga Komoditas, dan Artikel

- Menambahkan konteks/link BPS sebagai sumber data utuh.
- Fokus link BPS bukan potongan kecil, tapi satu halaman statistik/table BPS lengkap.
- Ada dokumen terkait di:
  - `docs/BPS_COMMODITY_COVERAGE.md`
  - `docs/BPS_REAL_DATA_LINKS.html`
- Error `Undefined variable $note` di halaman harga komoditas sudah ditangani sebelumnya.
- Halaman yang relevan:
  - `/harga-komoditas`
  - `/artikel/sumber-data-bps-agrilink`

### Peta

- Peta diminta hanya Indonesia.
- Endpoint dan view terkait yang perlu dipahami:
  - `routes/web.php`
  - `app/Http/Controllers/PublicController.php`
  - `app/Http/Controllers/Api/MapGeoJsonController.php`
  - `resources/views/public/map.blade.php`

### Produk dan Dashboard Buyer

- UI bagian produk sudah diarahkan lebih polished.
- Bagian "Ringkasan Pencarian" pada halaman produk diminta agar warnanya lebih balance.
- Dashboard buyer diminta agar ikon setiap item/produk berbeda, tidak semua ikon sama.
- Area terkait:
  - `resources/views/public/products`
  - `resources/views/buyer`
  - controller produk buyer/farmer bila ada penyesuaian data.

### Login dan Register

- Login/register dibuat dengan form tengah, mengikuti style yang user sukai dari `agri-system-Dewa`.
- Demo account panel ditambahkan di login.
- Akun demo yang ditampilkan:
  - Buyer: `buyer.demo@agri.com`
  - Petani: `petani.karawang@agri.com`
  - Distributor: `distributor.demo@agri.com`
  - Admin: `admin@agri.com`
  - Admin Master: `admin.master@agri.com`
- Password demo: `password`
- Auth attempt untuk akun demo sempat dicek dan valid.
- Area terkait:
  - `resources/views/auth/login.blade.php`
  - `resources/views/auth/register.blade.php`
  - `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
  - `app/Http/Controllers/Auth/RegisteredUserController.php`

## Admin Master vs Admin Biasa

### Role Baru

Role baru ditambahkan:

```text
admin_master - Admin Master
admin - Administrator
```

File yang diedit di project utama dan Dewa:

- `database/seeders/RoleSeeder.php`
- `database/seeders/AdminUserSeeder.php`
- `app/Models/User.php`

Helper yang ditambahkan ke `User`:

```php
public function isAdminMaster(): bool { return $this->hasRole('admin_master'); }
public function isAdminPanelUser(): bool { return $this->isAdmin() || $this->isAdminMaster(); }
```

Seeder sudah dijalankan di dua project:

```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AdminUserSeeder
```

Hasil cek DB:

- Project utama:
  - `admin.master@agri.com` role `admin_master`, id `11`
  - `admin@agri.com` role `admin`, id `1`
- Project Dewa:
  - `admin.master@agri.com` role `admin_master`, id `13`
  - `admin@agri.com` role `admin`, id `1`

### Routing Dashboard

Perubahan route utama:

- `/dashboard` sekarang mengarahkan:
  - `admin_master` ke `admin-master.dashboard`
  - `admin` ke `admin.dashboard`
  - role lain tetap ke dashboard masing-masing.
- Route baru:

```php
Route::middleware(['auth', 'role:admin_master'])
    ->prefix('admin-master')
    ->name('admin-master.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    });
```

- Admin group tetap:

```php
Route::middleware(['auth', 'role:admin,admin_master'])
    ->prefix('admin')
    ->name('admin.')
```

Artinya Admin Master boleh masuk modul admin biasa, tetapi akses kontrolnya dibatasi di controller.

### Dashboard Controller

File:

- `app/Http/Controllers/Admin/DashboardController.php`
- `../agri-system-Dewa/app/Http/Controllers/Admin/DashboardController.php`

Perubahan:

- Return type menjadi `View|RedirectResponse`.
- Jika Admin Master membuka `admin.dashboard`, diarahkan ke `admin-master.dashboard`.
- Isi dashboard tetap memakai view admin yang sama, tetapi labelnya dinamis:
  - `Dashboard Admin Master`
  - `Dashboard Admin`

### User Management Permissions

File:

- `app/Http/Controllers/Admin/UserManagementController.php`
- `../agri-system-Dewa/app/Http/Controllers/Admin/UserManagementController.php`

Aturan yang sudah diterapkan:

- Admin Master:
  - Bisa mengatur role semua user.
  - Bisa mengontrol Admin biasa.
  - Tidak bisa menghapus/mengubah status dirinya sendiri.
  - Tidak bisa menghapus akun Admin Master.
  - Tidak bisa mengontrol Admin Master lain.
- Admin biasa:
  - Bisa mengelola user non-admin.
  - Tidak bisa edit, update, delete, toggle status Admin biasa.
  - Tidak bisa edit, update, delete, toggle status Admin Master.
  - Tidak bisa assign role `admin` atau `admin_master`.
  - Form role untuk Admin biasa tidak menampilkan `admin` dan `admin_master`.

Helper controller penting:

```php
private function availableRolesFor(User $actor)
private function roleCanBeAssigned(User $actor, int $roleId): bool
private function canManageUser(User $actor, User $target): bool
private function abortIfCannotManage(User $target): void
```

### View Admin User

File project utama yang diedit:

- `resources/views/admin/_sidebar.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/users/show.blade.php`
- `resources/views/admin/users/create.blade.php`
- `resources/views/admin/users/edit.blade.php`

File Dewa yang diedit/diganti:

- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/users/show.blade.php`
- `resources/views/admin/users/create.blade.php`
- `resources/views/admin/users/edit.blade.php`

UI behavior:

- Admin Master mendapat label "Master".
- Tombol Edit/Toggle/Delete hanya muncul jika aktor punya izin.
- Jika tidak punya izin, UI menampilkan status `Terkunci`.
- Sidebar Dashboard:
  - Admin Master ke `admin-master.dashboard`.
  - Admin biasa ke `admin.dashboard`.

### Sidebar Umum

Bug potensial sudah dicegah:

- Sebelumnya beberapa view memakai:

```php
@include(auth()->user()->role->name . '._sidebar')
```

- Untuk `admin_master`, ini akan mencari `admin_master._sidebar` dan bisa error.
- Sudah dipatch agar Admin Master memakai sidebar admin:

```php
$sidebarRole = auth()->user()->isAdminMaster() ? 'admin' : auth()->user()->role->name
```

File yang dipatch:

- `resources/views/profile/edit.blade.php`
- `resources/views/chat/index.blade.php`
- `resources/views/notifications/index.blade.php`

## Validasi Yang Sudah Dilakukan

### Artisan dan Syntax

Dijalankan di project utama dan Dewa:

```bash
php -l app\Http\Controllers\Admin\UserManagementController.php
php -l app\Http\Controllers\Admin\DashboardController.php
php artisan route:list --path=admin-master
php artisan view:cache
php artisan test
```

Hasil:

- Syntax controller bersih.
- Route `admin-master/dashboard` terdaftar.
- Blade cache sukses.
- Test Laravel default lolos: 2 tests passed di kedua project.

### HTTP Manual Test

Di `http://127.0.0.1:8000`:

- Login `admin.master@agri.com` / `password`
  - Redirect ke `/admin-master/dashboard`
  - Halaman mengandung `Dashboard Admin Master`
- Login `admin@agri.com` / `password`
  - Redirect ke `/admin/dashboard`
  - Halaman mengandung `Dashboard Admin`
- Admin biasa membuka `/admin-master/dashboard`
  - Status `403`
- Admin Master membuka `/admin/dashboard`
  - Redirect ke `/admin-master/dashboard`
- Admin Master membuka edit Admin biasa:
  - `/admin/users/1/edit`
  - Status `200`
- Admin biasa membuka edit Admin Master:
  - `/admin/users/11/edit`
  - Status `403`

## Catatan Penting Untuk Codex Berikutnya

- Jangan reset atau revert perubahan user.
- Jangan commit API key yang pernah dikirim user.
- Kalau menjalankan server, pastikan dulu folder mana yang aktif: `agri-system` atau `agri-system-Dewa`.
- Jika browser sedang di `http://localhost/artikel/sumber-data-bps-agrilink`, kemungkinan Apache/XAMPP sedang melayani project lewat `localhost`; cek document root sebelum menyimpulkan.
- Untuk task UI, pertahankan gaya form tengah dan dashboard yang lebih clean.
- Untuk task role/admin, jangan samakan Admin Master dengan Admin biasa. Mereka berbagi beberapa modul, tapi hak kontrol berbeda.
- Jika menambah role baru lagi, cek:
  - seeder role
  - dashboard redirect
  - middleware route
  - user management permission
  - sidebar include berbasis role
  - register publik agar role internal tidak bocor.

## Update 2026-06-28: Audit Pupuk, Laporan, Webhook, dan Peta

Request terbaru user:

- Perbaiki error:
  - `/petani/pupuk-subsidi`
  - `/admin/laporan/distribusi-pupuk`
- Amankan webhook pembayaran.
- Audit `FertilizerOrderController` yang orphan.
- Ubah popup peta agar sesuai komoditas/sayuran dan gunakan placeholder jika gambar belum ada.

Perubahan yang sudah dilakukan:

- `app/Http/Controllers/Farmer/FertilizerController.php`
  - Menghapus pemanggilan `$this->middleware()` dari constructor karena Laravel 12 project ini tidak menyediakan method tersebut di base controller.
  - Dependency `FertilizerQuotaService` tetap dipakai.
- `routes/web.php`
  - Menambahkan `Route::middleware('farmer.verified')` khusus route `pupuk-subsidi`.
  - Route group `auth` dan `role:farmer` tetap menjadi proteksi utama untuk area petani.
- `app/Services/FertilizerQuotaService.php`
  - Memperbaiki laporan distribusi pupuk agar kompatibel SQLite/MySQL/PostgreSQL.
  - SQLite memakai `CAST(strftime('%m', fertilizer_transactions.dispensed_at) AS INTEGER)`.
  - MySQL tetap memakai `MONTH(...)`.
- `app/Http/Controllers/Api/PaymentWebhookController.php`
  - Webhook sekarang fail-closed.
  - Request tanpa secret/signature valid ditolak.
  - Support struktur validasi Midtrans dan Xendit.
  - Midtrans: validasi `signature_key = sha512(order_id + status_code + gross_amount + server_key)`.
  - Xendit: validasi header `x-callback-token`.
  - Tidak log payload mentah.
  - Validasi nominal pembayaran melawan `orders.total_amount` sebelum update status.
- `config/services.php`
  - Menambahkan konfigurasi `services.payment.webhook_gateway`.
  - Menambahkan `services.xendit.callback_token`.
- `.env.example`
  - Menambahkan variabel:
    - `PAYMENT_WEBHOOK_GATEWAY`
    - `MIDTRANS_SERVER_KEY`
    - `MIDTRANS_CLIENT_KEY`
    - `MIDTRANS_IS_PRODUCTION`
    - `XENDIT_CALLBACK_TOKEN`
- `app/Services/OrderService.php`
  - Webhook valid duplikat untuk order yang sudah paid dibuat idempotent jika payment reference sama.
  - Jika reference berbeda, tetap ditolak.
- `app/Http/Controllers/FertilizerOrderController.php`
  - Dihapus setelah audit `rg` menunjukkan tidak ada route/referensi.
- `app/Services/FertilizerService.php`
  - Dihapus karena hanya dipakai oleh controller pupuk legacy yang sudah dihapus.
- `app/Http/Controllers/Api/MapGeoJsonController.php`
  - Produk/farmer GeoJSON sekarang membawa `commodity_key` dan `image`.
  - Jika `products.main_image` ada, itu dipakai lebih dulu.
  - Jika kosong, sistem mencari gambar komoditas di `public/images/commodities/`.
  - Jika gambar komoditas belum tersedia, fallback ke placeholder.
  - Cache key GeoJSON dibump ke `v2`.
- `resources/views/components/leaflet-map.blade.php`
  - Popup product/farmer sekarang menampilkan gambar.
  - Popup product menampilkan nama, harga, stok, petani, kategori, lokasi, dan tombol detail.
  - Popup farmer menampilkan komoditas, kelompok, lokasi, dan ringkasan produk aktif.
  - Broken image dicegah dengan fallback ke placeholder.
- `public/images/commodities/placeholder.svg`
  - Placeholder komoditas dibuat.
- `docs/MAP_COMMODITY_IMAGE_ASSETS.md`
  - Daftar aset gambar yang perlu disediakan user.
- `tests/Feature/PaymentWebhookTest.php`
  - Test webhook tanpa signature valid ditolak dan order tetap pending.
  - Test signature Midtrans valid menandai order paid.

Validasi yang sudah dijalankan:

- `php -l app\Http\Controllers\Farmer\FertilizerController.php`
- `php -l app\Services\FertilizerQuotaService.php`
- `php -l app\Http\Controllers\Api\PaymentWebhookController.php`
- `php -l app\Http\Controllers\Api\MapGeoJsonController.php`
- `php -l app\Services\OrderService.php`
- `php artisan route:list --path=petani/pupuk-subsidi`
- `php artisan route:list --path=admin/laporan/distribusi-pupuk`
- `php artisan route:list --path=api/payment/webhook`
- `php artisan view:cache`
- `php artisan config:clear`
- `php artisan cache:clear`
- `php artisan test`
- `npm run build`

HTTP check di `127.0.0.1:8000`:

- Login `petani.karawang@agri.com` lalu buka `/petani/pupuk-subsidi`: status 200.
- Login `admin@agri.com` lalu buka `/admin/laporan/distribusi-pupuk`: status 200.
- `/api/map/combined` sudah mengeluarkan produk dengan:
  - `commodity_key`
  - `image`
  - fallback `images/commodities/placeholder.svg`

Gambar komoditas yang perlu user siapkan berdasarkan data aktif:

- `public/images/commodities/gabah.webp`
- `public/images/commodities/beras.webp`
- `public/images/commodities/bawang-merah.webp`
- `public/images/commodities/cabai-merah.webp`
- `public/images/commodities/kangkung.webp`

Ukuran ideal: `800 x 600 px`, rasio 4:3, format paling disarankan `.webp`.
