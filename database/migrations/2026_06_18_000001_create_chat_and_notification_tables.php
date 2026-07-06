<?php

// =============================================================================
// MIGRATION 5: Real-time Chat & Notification System
// =============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Chat Messages (direct messages between any two users) ---
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_id')->constrained('users')->cascadeOnDelete();
            $table->text('pesan');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['from_id', 'to_id']);
            $table->index(['to_id', 'is_read']);
        });

        // --- Notifications (system / stock / price / chat alerts per user) ---
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipe', ['arrived', 'pengiriman', 'price', 'low_stock', 'stok', 'chat', 'info', 'alert'])->default('info');
            $table->string('judul');
            $table->text('pesan');
            $table->string('link')->nullable();
            $table->boolean('dibaca')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'dibaca']);
            $table->index(['user_id', 'tipe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('chat_messages');
    }
};
