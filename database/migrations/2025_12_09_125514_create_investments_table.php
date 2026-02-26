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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('stock_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_account_id')->constrained()->onDelete('restrict');
            $table->string('reference_number')->unique();
            $table->decimal('amount', 15, 2); // Investment amount
            $table->decimal('shares', 15, 8); // Number of shares purchased
            $table->decimal('purchase_price', 15, 2); // Price per share at purchase
            $table->decimal('current_value', 15, 2)->default(0); // Current investment value
            $table->decimal('profit_loss', 15, 2)->default(0); // Total profit/loss
            $table->decimal('profit_loss_percentage', 8, 4)->default(0);
            $table->decimal('total_profit_paid', 15, 2)->default(0); // Total profit distributed
            $table->enum('status', [
                'pending',
                'active',
                'completed',
                'cancelled',
                'liquidated'
            ])->default('pending');
            $table->enum('investment_type', ['long_term', 'short_term', 'day_trade'])->default('long_term');
            $table->integer('duration_days')->nullable(); // Expected duration
            $table->decimal('roi_percentage', 8, 4)->nullable(); // Expected ROI %
            $table->date('maturity_date')->nullable(); // When investment matures
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('admin_notes')->nullable();
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
        Schema::dropIfExists('investments');
    }
};
