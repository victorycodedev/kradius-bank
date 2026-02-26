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
        Schema::table('users', function (Blueprint $table) {
            $table->after('password', function (Blueprint $table) {
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->json('security_questions')->nullable();
                $table->string('phone_number')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('zip_code')->nullable();
                $table->string('country')->nullable();
                $table->string('kyc_status')->default('pending'); // pending. verified, rejected
                $table->string('kyc_document_type')->nullable(); //passport, driver_license, national_id)
                $table->string('kyc_document_number')->nullable();
                $table->string('profile_photo_path')->nullable();
                $table->string('pin')->nullable();
                $table->boolean('biometric_enabled')->default(false);
                $table->boolean('two_factor_enabled')->default(false);
                $table->string('account_status')->default('active'); //(active, suspended, closed)
                $table->date('last_login_at')->nullable();
                $table->integer('login_attempts')->default(0);
                $table->date('locked_until')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};