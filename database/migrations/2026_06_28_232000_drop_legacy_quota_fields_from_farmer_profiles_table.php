<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $legacyColumns = [
        'urea_quota_kg',
        'npk_quota_kg',
        'urea_used_kg',
        'npk_used_kg',
        'quota_year',
        'quota_season',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('farmer_profiles')) {
            return;
        }

        Schema::table('farmer_profiles', function (Blueprint $table) {
            foreach ($this->legacyColumns as $column) {
                if (Schema::hasColumn('farmer_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('farmer_profiles')) {
            return;
        }

        Schema::table('farmer_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('farmer_profiles', 'urea_quota_kg')) {
                $table->integer('urea_quota_kg')->default(0);
            }

            if (!Schema::hasColumn('farmer_profiles', 'npk_quota_kg')) {
                $table->integer('npk_quota_kg')->default(0);
            }

            if (!Schema::hasColumn('farmer_profiles', 'urea_used_kg')) {
                $table->integer('urea_used_kg')->default(0);
            }

            if (!Schema::hasColumn('farmer_profiles', 'npk_used_kg')) {
                $table->integer('npk_used_kg')->default(0);
            }

            if (!Schema::hasColumn('farmer_profiles', 'quota_year')) {
                $table->year('quota_year')->nullable();
            }

            if (!Schema::hasColumn('farmer_profiles', 'quota_season')) {
                $table->string('quota_season')->nullable();
            }
        });
    }
};
