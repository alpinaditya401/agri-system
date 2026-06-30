<?php

// =============================================================================
// MIGRATION 3: Subsidized Fertilizer Distribution System
// =============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // --- Fertilizer Types ---
        Schema::create('fertilizer_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();      // UREA, NPK, ZA, SP36
            $table->string('name');
            $table->decimal('subsidy_price_per_kg', 10, 2);
            $table->decimal('market_price_per_kg', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // --- Distributor Fertilizer Stock (Warehouse) ---
        Schema::create('fertilizer_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('fertilizer_type_id')->constrained()->restrictOnDelete();
            $table->integer('stock_kg')->default(0);
            $table->integer('reserved_kg')->default(0); // reserved for pending orders
            $table->string('batch_number')->nullable();
            $table->date('received_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->index(['distributor_id', 'fertilizer_type_id']);
        });

        // --- Farmer Fertilizer Quotas (per year/season) ---
        Schema::create('fertilizer_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('fertilizer_type_id')->constrained()->restrictOnDelete();
            $table->year('year');
            $table->string('season', 10);              // MT1, MT2
            $table->integer('allocated_kg');           // total quota allocated
            $table->integer('used_kg')->default(0);    // amount already redeemed
            $table->integer('remaining_kg')            // computed: allocated - used
                ->storedAs('allocated_kg - used_kg');
            $table->timestamp('quota_expires_at')->nullable();
            $table->foreignId('allocated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();


            $table->index(['farmer_id', 'year', 'season']);
        });

        // --- Fertilizer Transactions (Purchases by Farmers) ---
        Schema::create('fertilizer_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('farmer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('distributor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('fertilizer_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('fertilizer_quota_id')->constrained()->restrictOnDelete();

            $table->integer('requested_kg');
            $table->integer('approved_kg')->nullable();
            $table->decimal('price_per_kg', 10, 2);
            $table->decimal('total_amount', 12, 2)->nullable();

            $table->enum('status', [
                'pending',          // Farmer submitted request
                'approved',         // Distributor approved
                'rejected',         // Rejected (quota exceeded or other)
                'dispensed',        // Physically given to farmer
                'cancelled',        // Cancelled by farmer
            ])->default('pending');

            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('dispensed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['farmer_id', 'status']);
            $table->index(['distributor_id', 'status']);
        });

        // =============================================================================
        // MIGRATION 4: BPS API Commodity Price System
        // =============================================================================

        // --- Commodity Price Cache (from BPS API) ---
        Schema::create('commodity_prices', function (Blueprint $table) {
            $table->id();
            $table->string('commodity_name');
            $table->string('commodity_code')->nullable();   // BPS internal code
            $table->string('category');                     // pangan, hortikultura, dll
            $table->decimal('price', 12, 2);
            $table->string('unit', 20)->default('kg');
            $table->string('region')->nullable();
            $table->string('region_code')->nullable();
            $table->string('source')->default('BPS');
            $table->date('price_date');
            $table->json('raw_data')->nullable();           // full raw BPS response
            $table->timestamps();

            $table->index(['commodity_name', 'price_date']);
            $table->index(['category', 'price_date']);
            $table->index('price_date');
        });

        // --- BPS API Fetch Log ---
        Schema::create('bps_fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->enum('status', ['success', 'failed', 'partial']);
            $table->integer('records_fetched')->default(0);
            $table->text('error_message')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();
        });

        // --- Articles / Blog ---
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->string('category')->nullable();        // berita, tips, harga, kebijakan
            $table->json('tags')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
        Schema::dropIfExists('bps_fetch_logs');
        Schema::dropIfExists('commodity_prices');
        Schema::dropIfExists('fertilizer_transactions');
        Schema::dropIfExists('fertilizer_quotas');
        Schema::dropIfExists('fertilizer_stocks');
        Schema::dropIfExists('fertilizer_types');
    }
};