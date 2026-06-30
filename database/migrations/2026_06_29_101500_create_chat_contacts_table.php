<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chat_contacts')) {
            Schema::create('chat_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('contact_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('label')->nullable();
                $table->boolean('is_pinned')->default(false);
                $table->timestamp('last_opened_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'contact_user_id']);
                $table->index(['user_id', 'is_pinned']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_contacts');
    }
};
