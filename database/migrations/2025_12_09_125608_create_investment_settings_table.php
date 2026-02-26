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
        Schema::create('investment_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('investments_enabled')->default(true);
            $table->decimal('minimum_investment_amount', 15, 2)->default(1000);
            $table->decimal('maximum_investment_amount', 15, 2)->default(10000000);
            $table->integer('max_active_investments_per_user')->default(10);
            $table->boolean('auto_profit_enabled')->default(false);
            $table->enum('auto_profit_frequency', ['daily', 'weekly', 'monthly'])->default('monthly');
            $table->decimal('default_roi_percentage', 8, 4)->default(5); // Default ROI %
            $table->integer('default_investment_duration_days')->default(365);
            $table->decimal('early_withdrawal_penalty', 8, 4)->default(5); // % penalty
            $table->boolean('allow_partial_withdrawal')->default(false);
            $table->boolean('require_kyc_for_investment')->default(true);
            $table->integer('min_account_age_days')->default(30);
            $table->text('investment_terms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_settings');
    }
};
