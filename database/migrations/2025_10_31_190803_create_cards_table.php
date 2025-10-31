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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_account_id')->constrained()->onDelete('cascade');
            $table->string('card_number')->unique();
            $table->string('card_holder_name');
            $table->enum('card_type', ['debit', 'credit', 'virtual'])->default('debit');
            $table->string('card_brand')->default('visa');
            $table->string('cvv');
            $table->string('card_pin')->nullable();
            $table->date('expiry_date');
            $table->decimal('daily_limit', 15, 2)->default(100000);
            $table->boolean('is_contactless_enabled')->default(true);
            $table->enum('card_status', ['active', 'blocked', 'expired', 'lost'])->default('active');
            $table->timestamp('blocked_at')->nullable();
            $table->string('block_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};