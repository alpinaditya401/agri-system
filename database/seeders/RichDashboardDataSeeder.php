<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CommodityPrice;
use App\Models\DistributorProfile;
use App\Models\FarmerProfile;
use App\Models\FertilizerQuota;
use App\Models\FertilizerStock;
use App\Models\FertilizerTransaction;
use App\Models\FertilizerType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RichDashboardDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $roles = Role::query()->pluck('id', 'name');
        $password = Hash::make('password');
        $adminId = User::query()
            ->whereHas('role', fn($query) => $query->whereIn('name', ['admin', 'admin_master']))
            ->value('id');

        $categories = $this->seedCategories();
        $buyers = $this->seedBuyers($roles, $password);
        $farmers = $this->seedFarmers($roles, $password, $adminId);
        $distributors = $this->seedDistributors($roles, $password);
        $fertilizers = $this->seedFertilizers();

        $products = $this->seedProducts($farmers, $categories);
        $this->seedCommodityPrices();
        $this->seedFertilizerFlow($farmers, $distributors, $fertilizers, $adminId);
        $this->seedOrders($buyers, $products);
        $this->seedCarts($buyers, $products);
    }

    private function seedCategories()
    {
        return collect([
            ['name' => 'Padi dan Beras', 'slug' => 'padi-beras', 'icon' => 'rice'],
            ['name' => 'Palawija', 'slug' => 'palawija', 'icon' => 'corn'],
            ['name' => 'Hortikultura', 'slug' => 'hortikultura', 'icon' => 'chili'],
            ['name' => 'Sayur dan Buah', 'slug' => 'sayur-buah', 'icon' => 'leaf'],
            ['name' => 'Pupuk dan Sarana', 'slug' => 'pupuk-sarana', 'icon' => 'package'],
        ])->mapWithKeys(function (array $category) {
            $model = ProductCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => 'Kategori marketplace Agrilink untuk demo dashboard dan laporan.',
                    'icon' => $category['icon'],
                ]
            );

            return [$category['slug'] => $model];
        });
    }

    private function seedBuyers($roles, string $password)
    {
        return collect([
            ['email' => 'buyer.demo@agri.com', 'name' => 'Pembeli Demo', 'phone' => '081200000001', 'province' => 'DKI Jakarta', 'district' => 'Jakarta Selatan'],
            ['email' => 'buyer.bandung@agri.com', 'name' => 'Toko Sayur Bandung', 'phone' => '081200000301', 'province' => 'Jawa Barat', 'district' => 'Bandung'],
            ['email' => 'buyer.surabaya@agri.com', 'name' => 'Warung Pangan Surabaya', 'phone' => '081200000302', 'province' => 'Jawa Timur', 'district' => 'Surabaya'],
            ['email' => 'buyer.denpasar@agri.com', 'name' => 'Katering Denpasar', 'phone' => '081200000303', 'province' => 'Bali', 'district' => 'Denpasar'],
        ])->mapWithKeys(function (array $buyer) use ($roles, $password) {
            $user = User::query()->updateOrCreate(
                ['email' => $buyer['email']],
                [
                    'role_id' => $roles['buyer'],
                    'name' => $buyer['name'],
                    'password' => $password,
                    'phone' => $buyer['phone'],
                    'address' => 'Alamat demo ' . $buyer['district'],
                    'province' => $buyer['province'],
                    'district' => $buyer['district'],
                    'is_active' => true,
                ]
            );

            return [$buyer['email'] => $user];
        });
    }

    private function seedFarmers($roles, string $password, ?int $adminId)
    {
        $farmers = [
            ['email' => 'petani.karawang@agri.com', 'name' => 'Petani Karawang', 'province' => 'Jawa Barat', 'district' => 'Karawang', 'sub_district' => 'Rawamerta', 'village' => 'Sukamerta', 'lat' => -6.3019, 'lng' => 107.3056, 'nik' => '3215010101010001', 'group' => 'Tani Makmur Karawang', 'land' => 2.50, 'commodity' => 'Padi/Gabah'],
            ['email' => 'petani.brebes@agri.com', 'name' => 'Petani Brebes', 'province' => 'Jawa Tengah', 'district' => 'Brebes', 'sub_district' => 'Wanasari', 'village' => 'Sidamulya', 'lat' => -6.8723, 'lng' => 109.0437, 'nik' => '3329010101010002', 'group' => 'Bawang Sejahtera', 'land' => 1.80, 'commodity' => 'Bawang Merah'],
            ['email' => 'petani.sleman@agri.com', 'name' => 'Petani Sleman', 'province' => 'DI Yogyakarta', 'district' => 'Sleman', 'sub_district' => 'Pakem', 'village' => 'Hargobinangun', 'lat' => -7.6569, 'lng' => 110.4203, 'nik' => '3404010101010003', 'group' => 'Merapi Tani', 'land' => 1.20, 'commodity' => 'Cabai dan Sayur'],
            ['email' => 'petani.cianjur@agri.com', 'name' => 'Petani Cianjur', 'province' => 'Jawa Barat', 'district' => 'Cianjur', 'sub_district' => 'Cilaku', 'village' => 'Rancagoong', 'lat' => -6.8275, 'lng' => 107.1429, 'nik' => '3203010101010004', 'group' => 'Beras Pandanwangi', 'land' => 3.10, 'commodity' => 'Beras Premium'],
            ['email' => 'petani.malang@agri.com', 'name' => 'Petani Malang', 'province' => 'Jawa Timur', 'district' => 'Malang', 'sub_district' => 'Pujon', 'village' => 'Pandesari', 'lat' => -7.8412, 'lng' => 112.4705, 'nik' => '3507010101010005', 'group' => 'Horti Batu Malang', 'land' => 1.45, 'commodity' => 'Tomat dan Sawi'],
            ['email' => 'petani.lombok@agri.com', 'name' => 'Petani Lombok Timur', 'province' => 'Nusa Tenggara Barat', 'district' => 'Lombok Timur', 'sub_district' => 'Sikur', 'village' => 'Kotaraja', 'lat' => -8.6353, 'lng' => 116.4217, 'nik' => '5203010101010006', 'group' => 'Cabai Lombok Timur', 'land' => 1.95, 'commodity' => 'Cabai Rawit'],
            ['email' => 'petani.gowa@agri.com', 'name' => 'Petani Gowa', 'province' => 'Sulawesi Selatan', 'district' => 'Gowa', 'sub_district' => 'Bontonompo', 'village' => 'Barembeng', 'lat' => -5.3215, 'lng' => 119.4208, 'nik' => '7306010101010007', 'group' => 'Jagung Gowa', 'land' => 4.20, 'commodity' => 'Jagung Pipilan'],
            ['email' => 'petani.karo@agri.com', 'name' => 'Petani Karo', 'province' => 'Sumatera Utara', 'district' => 'Karo', 'sub_district' => 'Berastagi', 'village' => 'Gundaling', 'lat' => 3.1947, 'lng' => 98.5089, 'nik' => '1211010101010008', 'group' => 'Sayur Karo', 'land' => 1.65, 'commodity' => 'Kol dan Wortel'],
            ['email' => 'petani.tababan@agri.com', 'name' => 'Petani Tabanan', 'province' => 'Bali', 'district' => 'Tabanan', 'sub_district' => 'Penebel', 'village' => 'Jatiluwih', 'lat' => -8.3712, 'lng' => 115.1312, 'nik' => '5102010101010009', 'group' => 'Subak Jatiluwih', 'land' => 2.75, 'commodity' => 'Padi Organik'],
            ['email' => 'petani.banyuwangi@agri.com', 'name' => 'Petani Banyuwangi', 'province' => 'Jawa Timur', 'district' => 'Banyuwangi', 'sub_district' => 'Muncar', 'village' => 'Tembokrejo', 'lat' => -8.4414, 'lng' => 114.3336, 'nik' => '3510010101010010', 'group' => 'Buah Banyuwangi', 'land' => 2.05, 'commodity' => 'Buah-buahan'],
        ];

        return collect($farmers)->mapWithKeys(function (array $data, int $index) use ($roles, $password, $adminId) {
            $farmer = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $roles['farmer'],
                    'name' => $data['name'],
                    'password' => $password,
                    'phone' => '081200001' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'address' => "{$data['village']}, {$data['district']}",
                    'province' => $data['province'],
                    'district' => $data['district'],
                    'sub_district' => $data['sub_district'],
                    'village' => $data['village'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'is_active' => true,
                ]
            );

            FarmerProfile::query()->updateOrCreate(
                ['user_id' => $farmer->id],
                [
                    'nik' => $data['nik'],
                    'farmer_group_id' => 'KT-' . strtoupper(Str::slug($data['district'], '')) . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'farmer_group_name' => $data['group'],
                    'land_area_hectares' => $data['land'],
                    'main_commodity' => $data['commodity'],
                    'verification_status' => 'verified',
                    'rejection_reason' => null,
                    'verified_at' => now()->subDays(30 - $index),
                    'verified_by' => $adminId,
                ]
            );

            return [$data['email'] => $farmer];
        });
    }

    private function seedDistributors($roles, string $password)
    {
        return collect([
            ['email' => 'distributor.demo@agri.com', 'name' => 'Kios Pupuk Tani Makmur', 'company' => 'Kios Pupuk Tani Makmur', 'province' => 'Jawa Barat', 'district' => 'Karawang', 'lat' => -6.3054, 'lng' => 107.3002, 'capacity' => 25000],
            ['email' => 'distributor.jateng@agri.com', 'name' => 'Gudang Subsidi Jateng', 'company' => 'PT Pupuk Jateng Sejahtera', 'province' => 'Jawa Tengah', 'district' => 'Brebes', 'lat' => -6.8760, 'lng' => 109.0470, 'capacity' => 32000],
            ['email' => 'distributor.jatim@agri.com', 'name' => 'Distributor Pupuk Jatim', 'company' => 'CV Pupuk Timur Raya', 'province' => 'Jawa Timur', 'district' => 'Malang', 'lat' => -7.9300, 'lng' => 112.6200, 'capacity' => 45000],
            ['email' => 'distributor.bali@agri.com', 'name' => 'Koperasi Pupuk Bali', 'company' => 'Koperasi Tani Bali', 'province' => 'Bali', 'district' => 'Tabanan', 'lat' => -8.5400, 'lng' => 115.1250, 'capacity' => 18000],
            ['email' => 'distributor.sulsel@agri.com', 'name' => 'Sentra Pupuk Sulsel', 'company' => 'UD Sentra Pupuk Sulsel', 'province' => 'Sulawesi Selatan', 'district' => 'Gowa', 'lat' => -5.3100, 'lng' => 119.4550, 'capacity' => 38000],
        ])->mapWithKeys(function (array $data, int $index) use ($roles, $password) {
            $distributor = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $roles['distributor'],
                    'name' => $data['name'],
                    'password' => $password,
                    'phone' => '081300000' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'address' => "Gudang {$data['district']}",
                    'province' => $data['province'],
                    'district' => $data['district'],
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'is_active' => true,
                ]
            );

            DistributorProfile::query()->updateOrCreate(
                ['user_id' => $distributor->id],
                [
                    'company_name' => $data['company'],
                    'license_number' => 'DIST-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT) . '-2026',
                    'storage_capacity_kg' => $data['capacity'],
                    'verification_status' => 'verified',
                ]
            );

            return [$data['email'] => $distributor];
        });
    }

    private function seedFertilizers()
    {
        return collect([
            ['code' => 'UREA', 'name' => 'Pupuk Urea', 'subsidy' => 2250, 'market' => 4500],
            ['code' => 'NPK', 'name' => 'Pupuk NPK Phonska', 'subsidy' => 2300, 'market' => 5200],
            ['code' => 'ZA', 'name' => 'Pupuk ZA', 'subsidy' => 1700, 'market' => 4200],
            ['code' => 'SP36', 'name' => 'Pupuk SP-36', 'subsidy' => 2400, 'market' => 5600],
        ])->mapWithKeys(function (array $fertilizer) {
            $model = FertilizerType::query()->updateOrCreate(
                ['code' => $fertilizer['code']],
                [
                    'name' => $fertilizer['name'],
                    'subsidy_price_per_kg' => $fertilizer['subsidy'],
                    'market_price_per_kg' => $fertilizer['market'],
                    'description' => 'Jenis pupuk subsidi untuk demo distribusi Agrilink.',
                    'is_active' => true,
                ]
            );

            return [$fertilizer['code'] => $model];
        });
    }

    private function seedProducts($farmers, $categories)
    {
        $products = [
            ['farmer' => 'petani.karawang@agri.com', 'cat' => 'padi-beras', 'name' => 'Gabah Kering Panen Karawang', 'price' => 6422, 'stock' => 2200, 'min' => 25, 'image' => 'images/commodities/gabah.webp'],
            ['farmer' => 'petani.cianjur@agri.com', 'cat' => 'padi-beras', 'name' => 'Beras Premium Cianjur', 'price' => 15600, 'stock' => 950, 'min' => 10, 'image' => 'images/commodities/beras.webp'],
            ['farmer' => 'petani.karawang@agri.com', 'cat' => 'padi-beras', 'name' => 'Beras Medium Karawang', 'price' => 13324, 'stock' => 1200, 'min' => 10, 'image' => 'images/commodities/beras.webp'],
            ['farmer' => 'petani.brebes@agri.com', 'cat' => 'hortikultura', 'name' => 'Bawang Merah Brebes', 'price' => 31000, 'stock' => 620, 'min' => 5, 'image' => 'images/commodities/bawang-merah.webp'],
            ['farmer' => 'petani.sleman@agri.com', 'cat' => 'hortikultura', 'name' => 'Cabai Merah Sleman', 'price' => 38500, 'stock' => 410, 'min' => 3, 'image' => 'images/commodities/cabai-merah.webp'],
            ['farmer' => 'petani.lombok@agri.com', 'cat' => 'hortikultura', 'name' => 'Cabai Rawit Lombok Timur', 'price' => 46500, 'stock' => 300, 'min' => 3, 'image' => 'images/commodities/cabai-merah.webp'],
            ['farmer' => 'petani.sleman@agri.com', 'cat' => 'sayur-buah', 'name' => 'Sayur Kangkung Segar', 'price' => 11464, 'stock' => 280, 'min' => 2, 'image' => 'images/commodities/kangkung.webp'],
            ['farmer' => 'petani.karo@agri.com', 'cat' => 'sayur-buah', 'name' => 'Bayam Karo Segar', 'price' => 9800, 'stock' => 260, 'min' => 2, 'image' => 'images/commodities/bayam.webp'],
            ['farmer' => 'petani.malang@agri.com', 'cat' => 'sayur-buah', 'name' => 'Tomat Sayur Malang', 'price' => 12200, 'stock' => 520, 'min' => 5, 'image' => 'images/commodities/placeholder.svg'],
            ['farmer' => 'petani.gowa@agri.com', 'cat' => 'palawija', 'name' => 'Jagung Pipilan Gowa', 'price' => 7200, 'stock' => 1800, 'min' => 25, 'image' => 'images/commodities/placeholder.svg'],
            ['farmer' => 'petani.tababan@agri.com', 'cat' => 'padi-beras', 'name' => 'Padi Organik Jatiluwih', 'price' => 7800, 'stock' => 900, 'min' => 20, 'image' => 'images/commodities/gabah.webp'],
            ['farmer' => 'petani.banyuwangi@agri.com', 'cat' => 'sayur-buah', 'name' => 'Paket Buah Banyuwangi', 'price' => 18500, 'stock' => 350, 'min' => 5, 'image' => 'images/commodities/placeholder.svg'],
        ];

        return collect($products)->mapWithKeys(function (array $product) use ($farmers, $categories) {
            $farmer = $farmers[$product['farmer']];
            $model = Product::withTrashed()->updateOrCreate(
                ['slug' => Str::slug($product['name'])],
                [
                    'farmer_id' => $farmer->id,
                    'category_id' => $categories[$product['cat']]->id,
                    'name' => $product['name'],
                    'description' => 'Produk demo berkualitas dari ' . $farmer->name . ' untuk marketplace Agrilink.',
                    'price_per_unit' => $product['price'],
                    'unit' => 'kg',
                    'stock_quantity' => $product['stock'],
                    'minimum_order' => $product['min'],
                    'main_image' => $product['image'],
                    'origin_province' => $farmer->province,
                    'origin_district' => $farmer->district,
                    'origin_lat' => $farmer->latitude,
                    'origin_lng' => $farmer->longitude,
                    'status' => 'active',
                    'is_featured' => true,
                ]
            );

            if ($model->trashed()) {
                $model->restore();
            }

            return [$product['name'] => $model];
        });
    }

    private function seedCommodityPrices(): void
    {
        $series = [
            ['code' => 'bps-gabah-gkp-petani', 'name' => 'Gabah Kering Panen (GKP) Tingkat Petani', 'cat' => 'Tanaman Pangan', 'base' => 6250, 'step' => 45],
            ['code' => 'bps-gabah-gkg-petani', 'name' => 'Gabah Kering Giling (GKG) Tingkat Petani', 'cat' => 'Tanaman Pangan', 'base' => 7350, 'step' => 55],
            ['code' => 'bps-beras-premium-penggilingan', 'name' => 'Beras Premium Tingkat Penggilingan', 'cat' => 'Pangan', 'base' => 15100, 'step' => 70],
            ['code' => 'bps-beras-medium-penggilingan', 'name' => 'Beras Medium Tingkat Penggilingan', 'cat' => 'Pangan', 'base' => 13200, 'step' => 65],
            ['code' => 'bps-cabai-merah-susenas', 'name' => 'Cabai Merah', 'cat' => 'Hortikultura', 'base' => 36000, 'step' => 420],
            ['code' => 'bps-cabai-rawit-susenas', 'name' => 'Cabai Rawit', 'cat' => 'Hortikultura', 'base' => 44500, 'step' => 520],
            ['code' => 'bps-bawang-merah-susenas', 'name' => 'Bawang Merah', 'cat' => 'Hortikultura', 'base' => 30000, 'step' => 330],
            ['code' => 'bps-sayur-susenas', 'name' => 'Sayur-sayuran', 'cat' => 'Hortikultura', 'base' => 10750, 'step' => 120],
            ['code' => 'bps-buah-susenas', 'name' => 'Buah-buahan', 'cat' => 'Hortikultura', 'base' => 17400, 'step' => 150],
            ['code' => 'demo-jagung-pipilan', 'name' => 'Jagung Pipilan', 'cat' => 'Palawija', 'base' => 6900, 'step' => 60],
            ['code' => 'demo-kedelai-lokal', 'name' => 'Kedelai Lokal', 'cat' => 'Palawija', 'base' => 11800, 'step' => 95],
            ['code' => 'demo-tomat-sayur', 'name' => 'Tomat Sayur', 'cat' => 'Hortikultura', 'base' => 11200, 'step' => 180],
        ];

        foreach ($series as $itemIndex => $item) {
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->startOfMonth()->subMonths($i)->endOfMonth();
                $wave = (int) round(sin(($i + $itemIndex) / 2) * ($item['base'] * 0.045));
                $price = max(1000, $item['base'] + ((11 - $i) * $item['step']) + $wave);

                CommodityPrice::query()->updateOrCreate(
                    [
                        'commodity_code' => $item['code'],
                        'region_code' => '0000',
                        'price_date' => $date->toDateString(),
                    ],
                    [
                        'commodity_name' => $item['name'],
                        'category' => $item['cat'],
                        'price' => $price,
                        'unit' => 'kg',
                        'region' => 'Nasional',
                        'source' => Str::startsWith($item['code'], 'demo-') ? 'Demo Agrilink' : 'BPS/Demo Agrilink',
                        'raw_data' => [
                            'method' => 'demo_dashboard_series',
                            'source_note' => Str::startsWith($item['code'], 'demo-')
                                ? 'Data demo internal Agrilink untuk kebutuhan presentasi dashboard.'
                                : 'Data demo presentasi yang mengikuti struktur cache seri harga BPS Agrilink.',
                            'source_tables' => $this->sourceTablesFor($item['code']),
                        ],
                    ]
                );
            }
        }
    }

    private function seedFertilizerFlow($farmers, $distributors, $fertilizers, ?int $adminId): void
    {
        foreach ($distributors->values() as $distIndex => $distributor) {
            foreach ($fertilizers as $code => $type) {
                $baseStock = match ($code) {
                    'UREA' => 12000,
                    'NPK' => 9500,
                    'ZA' => 5600,
                    default => 4800,
                };

                FertilizerStock::query()->updateOrCreate(
                    ['distributor_id' => $distributor->id, 'fertilizer_type_id' => $type->id],
                    [
                        'stock_kg' => $baseStock + ($distIndex * 1800),
                        'reserved_kg' => 200 + ($distIndex * 50),
                        'batch_number' => "DASH-{$code}-2026-" . ($distIndex + 1),
                        'received_date' => now()->subDays(20 + $distIndex)->toDateString(),
                        'expiry_date' => now()->addMonths(18)->toDateString(),
                    ]
                );
            }
        }

        $distributorList = $distributors->values();
        foreach ($farmers->values() as $farmerIndex => $farmer) {
            foreach ($fertilizers->take(3) as $code => $type) {
                $allocated = match ($code) {
                    'UREA' => 450,
                    'NPK' => 360,
                    default => 220,
                } + ($farmerIndex * 10);

                $used = min($allocated - 50, 80 + ($farmerIndex * 12));
                $quota = FertilizerQuota::query()->updateOrCreate(
                    [
                        'farmer_id' => $farmer->id,
                        'fertilizer_type_id' => $type->id,
                        'year' => now()->year,
                        'season' => 'MT1',
                    ],
                    [
                        'allocated_kg' => $allocated,
                        'used_kg' => $used,
                        'quota_expires_at' => now()->endOfYear(),
                        'allocated_by' => $adminId,
                    ]
                );

                $status = ['dispensed', 'approved', 'pending', 'dispensed'][$farmerIndex % 4];
                $approvedKg = $status === 'pending' ? null : min(120 + ($farmerIndex * 5), $allocated);
                $distributor = $distributorList[$farmerIndex % $distributorList->count()];
                $number = 'FERT-' . now()->year . '-' . str_pad((string) ($farmerIndex + 1), 4, '0', STR_PAD_LEFT) . '-' . $code;

                FertilizerTransaction::query()->updateOrCreate(
                    ['transaction_number' => $number],
                    [
                        'farmer_id' => $farmer->id,
                        'distributor_id' => $distributor->id,
                        'fertilizer_type_id' => $type->id,
                        'fertilizer_quota_id' => $quota->id,
                        'requested_kg' => min(160 + ($farmerIndex * 5), $allocated),
                        'approved_kg' => $approvedKg,
                        'price_per_kg' => $type->subsidy_price_per_kg,
                        'total_amount' => $approvedKg ? $approvedKg * (float) $type->subsidy_price_per_kg : null,
                        'status' => $status,
                        'approved_at' => in_array($status, ['approved', 'dispensed'], true) ? now()->subDays(8 - ($farmerIndex % 5)) : null,
                        'dispensed_at' => $status === 'dispensed' ? now()->subDays(2 + ($farmerIndex % 4)) : null,
                        'processed_by' => in_array($status, ['approved', 'dispensed'], true) ? $distributor->id : null,
                        'tracking_status' => $status === 'dispensed' ? 'arrived' : ($status === 'approved' ? 'on_the_way' : null),
                        'tracking_latitude' => $status !== 'pending' ? (float) $distributor->latitude + 0.01 : null,
                        'tracking_longitude' => $status !== 'pending' ? (float) $distributor->longitude + 0.01 : null,
                        'tracking_accuracy' => $status !== 'pending' ? 15 : null,
                        'tracking_note' => $status === 'pending' ? null : 'Lokasi demo distribusi pupuk diperbarui otomatis.',
                        'tracking_started_at' => $status !== 'pending' ? now()->subDays(3) : null,
                        'tracking_updated_at' => $status !== 'pending' ? now()->subMinutes(45 + $farmerIndex) : null,
                    ]
                );
            }
        }
    }

    private function seedOrders($buyers, $products): void
    {
        $statuses = ['completed', 'delivered', 'shipped', 'processing', 'confirmed', 'pending'];
        $buyerList = $buyers->values();
        $productList = $products->values();

        foreach (range(1, 24) as $index) {
            $product = $productList[($index - 1) % $productList->count()];
            $buyer = $buyerList[($index - 1) % $buyerList->count()];
            $quantity = 5 + (($index * 3) % 45);
            $subtotal = $quantity * (float) $product->price_per_unit;
            $shipping = $index % 4 === 0 ? 25000 : 0;
            $status = $statuses[$index % count($statuses)];
            $paid = ! in_array($status, ['pending', 'cancelled'], true);

            $order = Order::query()->updateOrCreate(
                ['order_number' => 'AGR-DEMO-' . now()->year . '-' . str_pad((string) $index, 4, '0', STR_PAD_LEFT)],
                [
                    'buyer_id' => $buyer->id,
                    'farmer_id' => $product->farmer_id,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shipping,
                    'tax_amount' => 0,
                    'total_amount' => $subtotal + $shipping,
                    'payment_status' => $paid ? 'paid' : 'pending',
                    'order_status' => $status,
                    'shipping_address' => $buyer->address ?? 'Alamat pembeli demo',
                    'shipping_method' => $shipping > 0 ? 'Kurir Agrilink' : 'Ambil di titik distribusi',
                    'tracking_number' => in_array($status, ['shipped', 'delivered', 'completed'], true) ? 'TRK-DEMO-' . str_pad((string) $index, 5, '0', STR_PAD_LEFT) : null,
                    'shipped_at' => in_array($status, ['shipped', 'delivered', 'completed'], true) ? now()->subDays(4) : null,
                    'delivered_at' => in_array($status, ['delivered', 'completed'], true) ? now()->subDays(1) : null,
                    'payment_method' => $paid ? 'Demo Auto-Paid' : 'Menunggu Pembayaran',
                    'payment_reference' => $paid ? 'DEMO-PAY-' . $index : null,
                    'payment_gateway' => 'demo',
                    'paid_at' => $paid ? now()->subDays(6) : null,
                    'buyer_notes' => 'Data pesanan demo untuk dashboard.',
                ]
            );

            OrderItem::query()->updateOrCreate(
                ['order_id' => $order->id, 'product_id' => $product->id],
                [
                    'product_name' => $product->name,
                    'price_per_unit' => $product->price_per_unit,
                    'unit' => $product->unit,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ]
            );
        }
    }

    private function seedCarts($buyers, $products): void
    {
        $productList = $products->values();

        foreach ($buyers->values() as $buyerIndex => $buyer) {
            foreach (range(0, 2) as $offset) {
                $product = $productList[($buyerIndex + $offset) % $productList->count()];
                Cart::query()->updateOrCreate(
                    ['buyer_id' => $buyer->id, 'product_id' => $product->id],
                    ['quantity' => 2 + $offset]
                );
            }
        }
    }

    private function sourceTablesFor(string $commodityCode): array
    {
        $tables = config('bps_sources.tables', []);
        $keys = config("bps_sources.commodities.{$commodityCode}", []);

        return collect($keys)
            ->map(fn(string $key) => $tables[$key] ?? null)
            ->filter()
            ->values()
            ->all();
    }
}
