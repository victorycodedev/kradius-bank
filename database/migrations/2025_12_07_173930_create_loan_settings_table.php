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
        Schema::create('loan_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('loan_applications_enabled')->default(true);
            $table->decimal('max_loan_amount', 15, 2)->default(1000000);
            $table->integer('max_active_loans_per_user')->default(1);
            $table->integer('min_account_age_days')->default(90); // Account must be X days old
            $table->decimal('min_account_balance', 15, 2)->default(0);
            $table->boolean('require_guarantor')->default(false);
            $table->integer('min_guarantors')->default(0);
            $table->json('required_documents')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_settings');
    }
};
