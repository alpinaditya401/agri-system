<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

        DB::table('users')->insertOrIgnore([
            'role_id'    => $adminRoleId,
            'name'       => 'Admin',
            'email'      => 'admin@agri.com',
            'password'   => Hash::make('password'),
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
