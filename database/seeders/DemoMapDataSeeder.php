<?php

namespace Database\Seeders;

use App\Models\DistributorProfile;
use App\Models\FarmerProfile;
use App\Models\FertilizerStock;
use App\Models\FertilizerType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoMapDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $roles = Role::query()->pluck('id', 'name');
        $password = Hash::make('password');

        $buyerRoleId = $roles['buyer'] ?? null;
        if ($buyerRoleId) {
            User::query()->updateOrCreate(
                ['email' => 'buyer.demo@agri.com'],
                [
                    'role_id' => $buyerRoleId,
                    'name' => 'Pembeli Demo',
                    'password' => $password,
                    'phone' => '081200000001',
                    'address' => 'Jl. Pasar Tani No. 1',
                    'province' => 'DKI Jakarta',
                    'district' => 'Jakarta Selatan',
                    'is_active' => true,
                ]
            );
        }

        $categories = collect([
            ['name' => 'Padi dan Beras', 'slug' => 'padi-beras', 'icon' => 'rice'],
            ['name' => 'Palawija', 'slug' => 'palawija', 'icon' => 'corn'],
            ['name' => 'Hortikultura', 'slug' => 'hortikultura', 'icon' => 'chili'],
            ['name' => 'Sayur dan Buah', 'slug' => 'sayur-buah', 'icon' => 'leaf'],
        ])->mapWithKeys(function (array $category) {
            $model = ProductCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => 'Kategori demo Agrilink untuk data pasar dan peta.',
                    'icon' => $category['icon'],
                ]
            );

            return [$category['slug'] => $model];
        });

        $adminId = User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'admin'))
            ->value('id');

        $farmers = collect([
            [
                'email' => 'petani.karawang@agri.com',
                'name' => 'Petani Karawang',
                'phone' => '081200000101',
                'province' => 'Jawa Barat',
                'district' => 'Karawang',
                'sub_district' => 'Rawamerta',
                'village' => 'Sukamerta',
                'latitude' => -6.30190000,
                'longitude' => 107.30560000,
                'profile' => [
                    'nik' => '3215010101010001',
                    'farmer_group_id' => 'KT-KRW-001',
                    'farmer_group_name' => 'Tani Makmur Karawang',
                    'land_area_hectares' => 2.50,
                    'main_commodity' => 'Padi/Gabah',
                ],
            ],
            [
                'email' => 'petani.brebes@agri.com',
                'name' => 'Petani Brebes',
                'phone' => '081200000102',
                'province' => 'Jawa Tengah',
                'district' => 'Brebes',
                'sub_district' => 'Wanasari',
                'village' => 'Sidamulya',
                'latitude' => -6.87230000,
                'longitude' => 109.04370000,
                'profile' => [
                    'nik' => '3329010101010002',
                    'farmer_group_id' => 'KT-BRB-002',
                    'farmer_group_name' => 'Bawang Sejahtera',
                    'land_area_hectares' => 1.80,
                    'main_commodity' => 'Bawang Merah',
                ],
            ],
            [
                'email' => 'petani.sleman@agri.com',
                'name' => 'Petani Sleman',
                'phone' => '081200000103',
                'province' => 'DI Yogyakarta',
                'district' => 'Sleman',
                'sub_district' => 'Pakem',
                'village' => 'Hargobinangun',
                'latitude' => -7.65690000,
                'longitude' => 110.42030000,
                'profile' => [
                    'nik' => '3404010101010003',
                    'farmer_group_id' => 'KT-SLM-003',
                    'farmer_group_name' => 'Merapi Tani',
                    'land_area_hectares' => 1.20,
                    'main_commodity' => 'Cabai dan Sayur',
                ],
            ],
        ])->mapWithKeys(function (array $data) use ($roles, $password, $adminId) {
            $profile = $data['profile'];
            unset($data['profile']);

            $farmer = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    ...$data,
                    'role_id' => $roles['farmer'],
                    'password' => $password,
                    'is_active' => true,
                ]
            );

            FarmerProfile::query()->updateOrCreate(
                ['user_id' => $farmer->id],
                [
                    ...$profile,
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                    'verified_by' => $adminId,
                ]
            );

            return [$data['email'] => $farmer];
        });

        $products = [
            [
                'farmer' => 'petani.karawang@agri.com',
                'category' => 'padi-beras',
                'name' => 'Gabah Kering Panen Karawang',
                'description' => 'Gabah panen baru dari kelompok tani Rawamerta.',
                'price_per_unit' => 6422,
                'unit' => 'kg',
                'stock_quantity' => 1800,
                'minimum_order' => 25,
            ],
            [
                'farmer' => 'petani.karawang@agri.com',
                'category' => 'padi-beras',
                'name' => 'Beras Medium Karawang',
                'description' => 'Beras medium untuk kebutuhan rumah tangga dan warung.',
                'price_per_unit' => 13324,
                'unit' => 'kg',
                'stock_quantity' => 850,
                'minimum_order' => 10,
            ],
            [
                'farmer' => 'petani.brebes@agri.com',
                'category' => 'hortikultura',
                'name' => 'Bawang Merah Brebes',
                'description' => 'Bawang merah lokal Brebes dengan ukuran campur.',
                'price_per_unit' => 31000,
                'unit' => 'kg',
                'stock_quantity' => 420,
                'minimum_order' => 5,
            ],
            [
                'farmer' => 'petani.sleman@agri.com',
                'category' => 'hortikultura',
                'name' => 'Cabai Merah Sleman',
                'description' => 'Cabai merah segar dari lereng Merapi.',
                'price_per_unit' => 38500,
                'unit' => 'kg',
                'stock_quantity' => 260,
                'minimum_order' => 3,
            ],
            [
                'farmer' => 'petani.sleman@agri.com',
                'category' => 'sayur-buah',
                'name' => 'Sayur Kangkung Segar',
                'description' => 'Kangkung segar siap kirim untuk pasar lokal.',
                'price_per_unit' => 11464,
                'unit' => 'kg',
                'stock_quantity' => 160,
                'minimum_order' => 2,
            ],
        ];

        foreach ($products as $product) {
            $farmer = $farmers[$product['farmer']];
            $category = $categories[$product['category']];
            $slug = Str::slug($product['name']);

            $demoProduct = Product::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                [
                    'farmer_id' => $farmer->id,
                    'category_id' => $category->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price_per_unit' => $product['price_per_unit'],
                    'unit' => $product['unit'],
                    'stock_quantity' => $product['stock_quantity'],
                    'minimum_order' => $product['minimum_order'],
                    'origin_province' => $farmer->province,
                    'origin_district' => $farmer->district,
                    'origin_lat' => $farmer->latitude,
                    'origin_lng' => $farmer->longitude,
                    'status' => 'active',
                    'is_featured' => true,
                ]
            );

            if ($demoProduct->trashed()) {
                $demoProduct->restore();
            }
        }

        $distributor = User::query()->updateOrCreate(
            ['email' => 'distributor.demo@agri.com'],
            [
                'role_id' => $roles['distributor'],
                'name' => 'Kios Pupuk Tani Makmur',
                'password' => $password,
                'phone' => '081200000201',
                'address' => 'Jl. Raya Karawang No. 12',
                'province' => 'Jawa Barat',
                'district' => 'Karawang',
                'sub_district' => 'Karawang Barat',
                'village' => 'Nagasari',
                'latitude' => -6.30540000,
                'longitude' => 107.30020000,
                'is_active' => true,
            ]
        );

        DistributorProfile::query()->updateOrCreate(
            ['user_id' => $distributor->id],
            [
                'company_name' => 'Kios Pupuk Tani Makmur',
                'license_number' => 'DIST-KRW-2026-001',
                'storage_capacity_kg' => 25000,
                'verification_status' => 'verified',
            ]
        );

        $fertilizers = collect([
            ['code' => 'UREA', 'name' => 'Pupuk Urea', 'subsidy_price_per_kg' => 2250, 'market_price_per_kg' => 4500],
            ['code' => 'NPK', 'name' => 'Pupuk NPK Phonska', 'subsidy_price_per_kg' => 2300, 'market_price_per_kg' => 5200],
        ])->mapWithKeys(function (array $fertilizer) {
            $model = FertilizerType::query()->updateOrCreate(
                ['code' => $fertilizer['code']],
                [
                    'name' => $fertilizer['name'],
                    'subsidy_price_per_kg' => $fertilizer['subsidy_price_per_kg'],
                    'market_price_per_kg' => $fertilizer['market_price_per_kg'],
                    'description' => 'Data pupuk demo untuk distribusi Agrilink.',
                    'is_active' => true,
                ]
            );

            return [$fertilizer['code'] => $model];
        });

        foreach (['UREA' => 12000, 'NPK' => 9000] as $code => $stockKg) {
            FertilizerStock::query()->updateOrCreate(
                [
                    'distributor_id' => $distributor->id,
                    'fertilizer_type_id' => $fertilizers[$code]->id,
                ],
                [
                    'stock_kg' => $stockKg,
                    'reserved_kg' => 0,
                    'batch_number' => 'DEMO-' . $code . '-' . now()->format('Y'),
                    'received_date' => now()->subDays(14)->toDateString(),
                    'expiry_date' => now()->addYear()->toDateString(),
                ]
            );
        }
    }
}
