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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_type_id')->constrained()->onDelete('restrict');
            $table->string('reference_number')->unique();
            $table->decimal('amount', 15, 2); // Requested amount
            $table->decimal('approved_amount', 15, 2)->nullable(); // May differ from requested
            $table->decimal('interest_rate', 5, 2); // Rate at time of application
            $table->integer('duration_months'); // Loan duration
            $table->decimal('monthly_payment', 15, 2)->nullable(); // Calculated monthly payment
            $table->decimal('total_payable', 15, 2)->nullable(); // Total amount to repay
            $table->decimal('outstanding_balance', 15, 2)->default(0); // Remaining balance
            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'disbursed',
                'active',
                'completed',
                'defaulted',
                'cancelled'
            ])->default('pending');
            $table->text('purpose'); // Reason for loan
            $table->enum('employment_status', ['employed', 'self_employed', 'unemployed', 'retired'])->nullable();
            $table->decimal('monthly_income', 15, 2)->nullable();
            $table->text('additional_info')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('review_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamp('due_date')->nullable(); // Final repayment date
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
