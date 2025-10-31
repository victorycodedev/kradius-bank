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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_account_id')->constrained()->onDelete('cascade');
            $table->enum('transaction_type', ['debit', 'credit', 'transfer', 'withdrawal', 'deposit'])->default('debit');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('reference_number')->unique();
            $table->text('description')->nullable();
            $table->foreignId('recipient_account_id')->nullable()->constrained('user_accounts')->onDelete('set null');
            $table->enum('status', ['pending', 'pending_verification', 'completed', 'failed', 'reversed'])->default('pending');
            $table->enum('channel', ['mobile_app', 'atm', 'pos', 'web', 'bank_transfer'])->default('mobile_app');
            $table->json('metadata')->nullable();
            $table->integer('current_verification_step')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_account_id');
            $table->index('reference_number');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};