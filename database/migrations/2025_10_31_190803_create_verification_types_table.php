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
        Schema::create('verification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->boolean('is_percentage')->default(false);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_required')->default(true);
            $table->enum('applies_to', ['all', 'international', 'local', 'above_threshold'])->default('all');
            $table->decimal('threshold_amount', 15, 2)->nullable();
            $table->timestamps();

            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_types');
    }
};