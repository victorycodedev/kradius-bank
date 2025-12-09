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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique(); // AAPL, GOOGL, TSLA
            $table->string('name'); // Apple Inc, Alphabet Inc
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // Technology, Finance, Healthcare
            $table->string('logo_url')->nullable();
            $table->decimal('current_price', 15, 2); // Current stock price
            $table->decimal('opening_price', 15, 2)->nullable(); // Today's opening price
            $table->decimal('previous_close', 15, 2)->nullable(); // Yesterday's close
            $table->decimal('day_high', 15, 2)->nullable();
            $table->decimal('day_low', 15, 2)->nullable();
            $table->decimal('price_change', 15, 2)->default(0); // +/- from previous close
            $table->decimal('price_change_percentage', 8, 4)->default(0); // Percentage change
            $table->decimal('minimum_investment', 15, 2)->default(1000);
            $table->decimal('maximum_investment', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('investment_count')->default(0); // Total investments in this stock
            $table->decimal('total_invested_amount', 15, 2)->default(0);
            $table->json('metadata')->nullable(); // Additional data (market cap, etc)
            $table->timestamps();

            $table->index('symbol');
            $table->index(['is_active', 'is_featured']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
