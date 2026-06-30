<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'name');
        $password = Hash::make('password');

        $buyerId = $this->upsertUser([
            'role_id' => $roles['buyer'] ?? null,
            'name' => 'Pembeli Demo',
            'email' => 'buyer.demo@agri.com',
            'password' => $password,
            'phone' => '081200000001',
            'province' => 'DKI Jakarta',
            'district' => 'Jakarta Selatan',
            'latitude' => -6.2615,
            'longitude' => 106.8106,
        ]);

        $farmerId = $this->upsertUser([
            'role_id' => $roles['farmer'] ?? null,
            'name' => 'Petani Karawang',
            'email' => 'petani.karawang@agri.com',
            'password' => $password,
            'phone' => '081200000002',
            'province' => 'Jawa Barat',
            'district' => 'Karawang',
            'sub_district' => 'Rawamerta',
            'village' => 'Sukamerta',
            'latitude' => -6.3017,
            'longitude' => 107.3058,
        ]);

        $distributorId = $this->upsertUser([
            'role_id' => $roles['distributor'] ?? null,
            'name' => 'Kios Pupuk Tani Makmur',
            'email' => 'distributor.demo@agri.com',
            'password' => $password,
            'phone' => '081200000003',
            'province' => 'Jawa Barat',
            'district' => 'Karawang',
            'sub_district' => 'Telukjambe',
            'village' => 'Sukaluyu',
            'latitude' => -6.3222,
            'longitude' => 107.2944,
        ]);

        if ($farmerId) {
            DB::table('farmer_profiles')->updateOrInsert(
                ['user_id' => $farmerId],
                [
                    'nik' => '3215010101010001',
                    'farmer_group_id' => 'KT-DEMO-001',
                    'farmer_group_name' => 'Tani Makmur Demo',
                    'land_area_hectares' => 2.5,
                    'main_commodity' => 'Padi',
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        if ($distributorId) {
            DB::table('distributor_profiles')->updateOrInsert(
                ['user_id' => $distributorId],
                [
                    'company_name' => 'Kios Pupuk Tani Makmur',
                    'license_number' => 'DIST-DEMO-001',
                    'storage_capacity_kg' => 10000,
                    'verification_status' => 'verified',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        unset($buyerId);
    }

    private function upsertUser(array $data): ?int
    {
        if (empty($data['role_id'])) {
            return null;
        }

        DB::table('users')->updateOrInsert(
            ['email' => $data['email']],
            [
                ...$data,
                'is_active' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return DB::table('users')->where('email', $data['email'])->value('id');
    }
}
