<?php

// =============================================================================
// MIGRATION 1: Users, Roles, and Farmer Profiles
// =============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Roles Table ---
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();           // admin, farmer, buyer, distributor
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // --- Users Table ---
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();

            // Geographic coordinates (required for Farmer & Distributor)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();     // Kabupaten
            $table->string('sub_district')->nullable(); // Kecamatan
            $table->string('village')->nullable();      // Desa/Kelurahan

            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role_id', 'is_active']);
            $table->index(['latitude', 'longitude']);
        });

        // --- Farmer Profiles (extended info for role=farmer) ---
        Schema::create('farmer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            // Strict validation fields (NIK or Farmer Group ID required)
            $table->string('nik', 16)->nullable()->unique();  // National ID Number
            $table->string('farmer_group_id')->nullable();    // Nomor Kelompok Tani
            $table->string('farmer_group_name')->nullable();  // Nama Kelompok Tani

            $table->decimal('land_area_hectares', 8, 2)->nullable();
            $table->string('main_commodity')->nullable();     // padi, jagung, dll
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            // Fertilizer subsidy quota (kg per season)
            $table->integer('urea_quota_kg')->default(0);
            $table->integer('npk_quota_kg')->default(0);
            $table->integer('urea_used_kg')->default(0);
            $table->integer('npk_used_kg')->default(0);
            $table->year('quota_year')->nullable();
            $table->string('quota_season')->nullable(); // MT1, MT2 (Musim Tanam)

            $table->timestamps();

            $table->index('nik');
            $table->index('farmer_group_id');
            $table->index('verification_status');
        });

        // --- Distributor Profiles ---
        Schema::create('distributor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('license_number')->nullable();      // Nomor SIUP/Izin Distributor
            $table->integer('storage_capacity_kg')->default(0);
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_profiles');
        Schema::dropIfExists('farmer_profiles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
