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
        Schema::create('account_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_account_id')->constrained()->onDelete('cascade');
            $table->decimal('daily_transfer_limit', 15, 2)->default(500000);
            $table->decimal('daily_withdrawal_limit', 15, 2)->default(200000);
            $table->decimal('single_transaction_limit', 15, 2)->default(100000);
            $table->timestamp('updated_at');

            $table->unique('user_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_limits');
    }
};