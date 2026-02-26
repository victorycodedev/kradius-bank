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
        Schema::create('user_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_number')->unique();
            $table->enum('account_type', ['savings', 'current', 'fixed_deposit'])->default('savings');
            $table->decimal('balance', 20, 8)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('account_tier', ['basic', 'premium', 'gold'])->default('basic');
            $table->boolean('is_primary')->default(false);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->decimal('minimum_balance', 15, 2)->default(0);
            $table->boolean('frozen')->default(false);
            $table->timestamp('frozen_at')->nullable();
            $table->string('frozen_reason')->nullable();
            $table->enum('status', ['active', 'inactive', 'closed'])->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_accounts');
    }
};
