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
        Schema::create('stock_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 15, 2);
            $table->decimal('opening_price', 15, 2)->nullable();
            $table->decimal('closing_price', 15, 2)->nullable();
            $table->decimal('high', 15, 2)->nullable();
            $table->decimal('low', 15, 2)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->date('date');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['stock_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_price_histories');
    }
};
