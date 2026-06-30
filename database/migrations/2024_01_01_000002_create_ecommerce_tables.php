<?php

// =============================================================================
// MIGRATION 2: E-Commerce — Products, Orders, Order Items, Cart
// =============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Product Categories ---
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        // --- Products (uploaded by Farmers) ---
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_per_unit', 12, 2);
            $table->string('unit', 20)->default('kg');   // kg, ton, ikat, buah
            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_order')->default(1);
            $table->string('main_image')->nullable();

            // Geographic origin of the produce
            $table->string('origin_province')->nullable();
            $table->string('origin_district')->nullable();
            $table->decimal('origin_lat', 10, 8)->nullable();
            $table->decimal('origin_lng', 11, 8)->nullable();

            $table->enum('status', ['draft', 'active', 'inactive', 'sold_out'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['farmer_id', 'status']);
            $table->index(['category_id', 'status']);
        });

        // --- Product Images (gallery) ---
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // --- Shopping Cart ---
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['buyer_id', 'product_id']);
        });

        // --- Orders (Invoices) ---
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();       // e.g. AGR-20240115-0001
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('farmer_id')->constrained('users')->restrictOnDelete();

            $table->decimal('subtotal', 14, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 14, 2);

            $table->enum('payment_status', [
                'pending', 'paid', 'failed', 'refunded'
            ])->default('pending');

            $table->enum('order_status', [
                'pending',          // Menunggu konfirmasi
                'confirmed',        // Dikonfirmasi petani
                'processing',       // Sedang dikemas
                'shipped',          // Dalam pengiriman
                'delivered',        // Terkirim
                'completed',        // Selesai
                'cancelled',        // Dibatalkan
                'disputed',         // Dipersengketakan
            ])->default('pending');

            // Shipping
            $table->text('shipping_address');
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Payment
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('buyer_notes')->nullable();
            $table->text('farmer_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['buyer_id', 'order_status']);
            $table->index(['farmer_id', 'order_status']);
            $table->index('order_number');
        });

        // --- Order Items ---
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name');              // snapshot at time of order
            $table->decimal('price_per_unit', 12, 2);   // snapshot at time of order
            $table->string('unit', 20);
            $table->integer('quantity');
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
