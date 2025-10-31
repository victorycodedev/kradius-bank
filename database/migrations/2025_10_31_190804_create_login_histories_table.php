<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->enum('device_type', ['ios', 'android', 'web', 'unknown'])->default('unknown');
            $table->string('device_name')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};