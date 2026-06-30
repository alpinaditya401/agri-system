<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_gateway')) {
                $table->string('payment_gateway')->nullable();
            }

            if (!Schema::hasColumn('orders', 'payment_token')) {
                $table->string('payment_token')->nullable();
            }

            if (!Schema::hasColumn('orders', 'payment_checkout_url')) {
                $table->text('payment_checkout_url')->nullable();
            }

            if (!Schema::hasColumn('orders', 'payment_expires_at')) {
                $table->timestamp('payment_expires_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['payment_gateway', 'payment_token', 'payment_checkout_url', 'payment_expires_at'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
