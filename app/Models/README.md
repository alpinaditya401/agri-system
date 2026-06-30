# 🌾 Sistem Informasi dan Perdagangan Pertanian — Laravel Blueprint

A comprehensive Laravel backend for an integrated Agriculture Information and Trading platform covering commodity prices, e-commerce, and subsidized fertilizer distribution.

---

## 📁 File Structure

```
agri-system/
├── database/migrations/
│   ├── ..._create_users_and_roles_tables.php       # Module 1: Auth & RBAC
│   ├── ..._create_ecommerce_tables.php             # Module 3: E-Commerce
│   └── ..._create_fertilizer_and_bps_tables.php   # Modules 2, 4: BPS + Fertilizer
│
├── app/
│   ├── Models/
│   │   └── Models.php              # User, FarmerProfile, FertilizerQuota, etc.
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── RegisteredUserController.php   # Role-based registration
│   │   │   ├── Farmer/
│   │   │   │   └── FertilizerController.php       # Quota validation + purchase
│   │   │   └── Api/
│   │   │       └── MapGeoJsonController.php       # Module 5: GIS / GeoJSON
│   │   ├── Requests/
│   │   │   └── RegisterRequest.php                # NIK & role-conditional validation
│   │   └── Middleware/
│   │       └── RoleMiddleware.php                 # CheckRole + EnsureFarmerVerified
│   │
│   ├── Services/
│   │   ├── BpsApiService.php                      # Module 2: BPS API integration
│   │   ├── FertilizerQuotaService.php             # Module 4: Quota logic
│   │   └── OrderService.php                       # Module 3: Checkout + stock deduction
│   │
│   └── Console/
│       ├── Kernel.php                             # Scheduler (Laravel 10)
│       └── Commands/
│           └── FetchBpsCommodityPrices.php        # Daily BPS cron command
│
├── routes/
│   └── web.php                                    # All routes (public, auth, role-based)
│
└── config/
    └── services.php                               # BPS API + Midtrans config keys
```

---

## 🏗️ Database Schema — Entity Relationships

```
roles ──< users ──< farmer_profiles
                 ├──< distributor_profiles
                 ├──< products ──< product_images
                 │             ──< order_items
                 ├──< carts
                 ├──< orders (buyer + farmer sides)
                 ├──< fertilizer_quotas ──< fertilizer_transactions
                 └──< fertilizer_stocks

fertilizer_types ──< fertilizer_quotas
                 ──< fertilizer_stocks
                 ──< fertilizer_transactions

commodity_prices  (populated by BPS cron job)
bps_fetch_logs    (audit trail for API calls)
articles          (blog/news)
```

---

## 🔐 Module 1: Auth & RBAC

### Roles
| Role        | Key Permissions                                              |
|-------------|--------------------------------------------------------------|
| `admin`     | Verify farmers, allocate quotas, manage all data, reports    |
| `farmer`    | List products, receive orders, request fertilizer subsidy    |
| `buyer`     | Browse products, add to cart, place orders, track shipping   |
| `distributor` | Manage fertilizer stock, approve/dispense farmer requests  |

### NIK Validation (Farmer-specific)
- `RegisterRequest` enforces: either `nik` (exactly 16 numeric digits) **or** `farmer_group_id` must be present
- NIK is validated with `regex:/^\d{16}$/` and `unique:farmer_profiles,nik`
- Cross-field validation in `withValidator()` catches the "both empty" case
- Farmer profiles start as `verification_status = pending` — admin must manually verify before subsidy access

---

## 📊 Module 2: BPS Commodity Price System

### Scheduler Flow
```
[Cron: * * * * *] → php artisan schedule:run
    → Command: bps:fetch-prices  (dailyAt 00:05)
        → BpsApiService::fetchAndCacheAll()
            → HTTP GET BPS API (per commodity)
            → Transform & upsert → commodity_prices table
            → Cache::forget (invalidate page cache)
            → Log result → bps_fetch_logs table
```

### Page Load Flow (zero live API calls)
```
User visits /harga-komoditas
    → PublicController::commodityPrices()
        → BpsApiService::getLatestPrices()
            → Cache::remember('commodity_prices_latest', 3600, ...)
                → SELECT from commodity_prices (already populated by cron)
```

### .env Keys Required
```
BPS_API_KEY=your_bps_webapi_key
BPS_API_BASE_URL=https://webapi.bps.go.id/v1/api
```

### Crontab Setup
```bash
* * * * * cd /var/www/your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🛒 Module 3: E-Commerce (C2C/B2C)

### Checkout Flow
```
Buyer adds to cart (CartController::add)
    → addToCart() validates stock > 0 & product.status = active
Buyer checks out (CartController::checkout)
    → OrderService::checkoutFromCart()
        → Group cart items by farmer_id
        → Per farmer group:
            ├── SELECT ... FOR UPDATE (lock product rows)
            ├── Validate stock >= requested quantity
            ├── Calculate: subtotal + shipping + 11% VAT
            ├── Create Order (status: pending, payment_status: pending)
            └── Create OrderItems (snapshot product name + price)
        → Delete cart items
Payment gateway callback → POST /api/payment/webhook
    → OrderService::confirmPayment()
        ├── Lock order row
        ├── Decrement product.stock_quantity
        ├── Auto-set status = sold_out if stock hits 0
        └── Fire OrderPaid event (notifications, receipts)
```

---

## 🌱 Module 4: Fertilizer Subsidy Distribution

### Security Layers (FertilizerController::store)
The quota check uses **`SELECT ... FOR UPDATE`** (row-level locking) to prevent race conditions in concurrent requests:

```
1. [Auth]     User must be authenticated + role=farmer
2. [Middleware] farmer.verified → profile.verification_status = verified
3. [Quota Lock] SELECT quota FOR UPDATE (prevents concurrent over-redemption)
4. [Existence] Quota row must exist for current year+season
5. [Expiry]   quota_expires_at must not have passed
6. [Remaining] remaining_kg > 0
7. [Amount]   requested_kg <= remaining_kg
8. [Stock]    distributor.available_stock >= requested_kg (stock - reserved)
9. [Duplicate] No existing pending/approved transaction for same type
10. [Commit]  Create FertilizerTransaction, increment distributor.reserved_kg
```

### Admin Quota Allocation
```
Admin → FertilizerQuotaAdminController::allocate()
    → FertilizerQuotaService::allocateQuota()
        → Verify farmer.verification_status = verified (throws if not)
        → UPSERT fertilizer_quotas (farmer_id, type, year, season)
```

---

## 🗺️ Module 5: GIS / Map

### GeoJSON Endpoints
| Endpoint                  | Description                              | Auth     |
|---------------------------|------------------------------------------|----------|
| `GET /api/map/farmers`    | Verified farmer locations                | Public   |
| `GET /api/map/distributors` | Distributor locations + stock summary  | Public   |
| `GET /api/map/products`   | Active product listings by origin coords | Public   |
| `GET /api/map/combined`   | All layers merged                        | Public   |

### Frontend Integration (Leaflet.js example)
```javascript
fetch('/api/map/combined')
  .then(r => r.json())
  .then(geojson => {
    L.geoJSON(geojson, {
      pointToLayer: (feature, latlng) => {
        const icons = { farmer: '🌾', distributor: '🏪', product: '🛒' };
        return L.marker(latlng).bindPopup(
          `<b>${feature.properties.name}</b><br>${feature.properties.district}`
        );
      }
    }).addTo(map);
  });
```

---

## ⚙️ Service Provider Bindings

Register services in `AppServiceProvider::register()`:

```php
$this->app->singleton(BpsApiService::class);
$this->app->singleton(FertilizerQuotaService::class);
$this->app->singleton(OrderService::class);
```

---

## 🚀 Setup Checklist

```bash
# 1. Run migrations
php artisan migrate

# 2. Seed roles and admin user
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AdminUserSeeder

# 3. Set up storage symlink (for product images)
php artisan storage:link

# 4. Register crontab (see above)
# Then test manually:
php artisan bps:fetch-prices --verbose

# 5. Test scheduler (dry run)
php artisan schedule:list
```

---

## 📦 Recommended Packages

| Package                        | Purpose                            |
|--------------------------------|------------------------------------|
| `laravel/sanctum`              | API token auth (mobile apps)       |
| `spatie/laravel-permission`    | Alternative RBAC (optional)        |
| `intervention/image`           | Product image resizing             |
| `maatwebsite/excel`            | Export quota/order reports to Excel|
| `barryvdh/laravel-dompdf`      | PDF invoice generation             |
| `midtrans/midtrans-php`        | Indonesian payment gateway         |
