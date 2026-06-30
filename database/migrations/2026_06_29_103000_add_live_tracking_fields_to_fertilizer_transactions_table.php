<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fertilizer_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('fertilizer_transactions', 'tracking_status')) {
                $table->string('tracking_status', 30)->nullable()->after('status');
            }
            if (! Schema::hasColumn('fertilizer_transactions', 'tracking_latitude')) {
                $table->decimal('tracking_latitude', 10, 8)->nullable()->after('tracking_status');
            }
            if (! Schema::hasColumn('fertilizer_transactions', 'tracking_longitude')) {
                $table->decimal('tracking_longitude', 11, 8)->nullable()->after('tracking_latitude');
            }
            if (! Schema::hasColumn('fertilizer_transactions', 'tracking_accuracy')) {
                $table->decimal('tracking_accuracy', 8, 2)->nullable()->after('tracking_longitude');
            }
            if (! Schema::hasColumn('fertilizer_transactions', 'tracking_note')) {
                $table->string('tracking_note')->nullable()->after('tracking_accuracy');
            }
            if (! Schema::hasColumn('fertilizer_transactions', 'tracking_started_at')) {
                $table->timestamp('tracking_started_at')->nullable()->after('tracking_note');
            }
            if (! Schema::hasColumn('fertilizer_transactions', 'tracking_updated_at')) {
                $table->timestamp('tracking_updated_at')->nullable()->after('tracking_started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fertilizer_transactions', function (Blueprint $table) {
            $columns = [
                'tracking_status',
                'tracking_latitude',
                'tracking_longitude',
                'tracking_accuracy',
                'tracking_note',
                'tracking_started_at',
                'tracking_updated_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('fertilizer_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
