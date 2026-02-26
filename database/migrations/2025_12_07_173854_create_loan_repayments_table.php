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
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reference_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->decimal('principal_amount', 15, 2); // Part of payment that reduces principal
            $table->decimal('interest_amount', 15, 2); // Part of payment that is interest
            $table->timestamp('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'partial'])->default('pending');
            $table->enum('payment_method', ['bank_transfer', 'card', 'deduction', 'cash'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['loan_id', 'status']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};
