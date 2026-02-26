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
        Schema::table('settings', function (Blueprint $table) {
            $table->after('backup_retention_days', function (Blueprint $table) {
                $table->string('coin')->default('USDT');
                $table->string('crypto_name')->default('Tether');
                $table->string('network')->default('TRC20');
                $table->string('wallet_address')->nullable();
                $table->json('more_crypto_attributes')->nullable();
                $table->string('account_holder_name')->nullable();
                $table->string('iban')->nullable();
                $table->string('swift')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('account_number')->nullable();
                $table->json('more_bank_attributes')->nullable();
                $table->boolean('enable_crypto_payment')->default(true);
                $table->boolean('enable_bank_payment')->default(true);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
