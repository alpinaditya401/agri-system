<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator'],
            ['name' => 'farmer', 'display_name' => 'Petani (Farmer)'],
            ['name' => 'buyer', 'display_name' => 'Pembeli (Buyer)'],
            ['name' => 'distributor', 'display_name' => 'Distributor Pupuk'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insertOrIgnore([
                'name' => $role['name'],
                'display_name' => $role['display_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}