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
        $adminMasterRoleId = DB::table('roles')->where('name', 'admin_master')->value('id');

        foreach ([
            ['name' => 'Admin', 'email' => 'admin@agri.com', 'role_id' => $adminRoleId],
            ['name' => 'Admin Master', 'email' => 'admin.master@agri.com', 'role_id' => $adminMasterRoleId],
        ] as $admin) {
            DB::table('users')->updateOrInsert(
                ['email' => $admin['email']],
                [
                    'role_id'    => $admin['role_id'],
                    'name'       => $admin['name'],
                    'password'   => Hash::make('password'),
                    'is_active'  => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
