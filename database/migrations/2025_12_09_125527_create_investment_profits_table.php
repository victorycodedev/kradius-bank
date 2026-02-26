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
        Schema::create('investment_profits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->onDelete('cascade');
            $table->string('reference_number')->unique();
            $table->decimal('amount', 15, 2); // Profit amount
            $table->enum('type', ['roi', 'dividend', 'capital_gain', 'manual'])->default('roi');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who paid
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_auto_generated')->default(false); // From cron job
            $table->timestamps();

            $table->index(['investment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_profits');
    }
};
