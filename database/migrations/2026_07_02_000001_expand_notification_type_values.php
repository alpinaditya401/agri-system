<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notifications MODIFY tipe ENUM('arrived','pengiriman','price','low_stock','stok','chat','info','alert') NOT NULL DEFAULT 'info'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        DB::table('notifications')->where('tipe', 'pengiriman')->update(['tipe' => 'arrived']);
        DB::table('notifications')->where('tipe', 'stok')->update(['tipe' => 'low_stock']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notifications MODIFY tipe ENUM('arrived','price','low_stock','chat','info','alert') NOT NULL DEFAULT 'info'");
        }
    }
};
