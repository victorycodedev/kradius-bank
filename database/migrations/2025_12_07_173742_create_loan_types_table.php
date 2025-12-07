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
        Schema::create('loan_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Personal Loan, Business Loan, etc.
            $table->text('description')->nullable();
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2); // Annual percentage rate
            $table->integer('min_duration_months')->default(1); // Minimum loan duration
            $table->integer('max_duration_months'); // Maximum loan duration
            $table->boolean('is_active')->default(true);
            $table->json('requirements')->nullable(); // Required documents, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_types');
    }
};
