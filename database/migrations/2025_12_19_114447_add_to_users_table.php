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
            $table->after('email', function (Blueprint $table) {
                $table->boolean('can_add_card')->default(false);
                $table->boolean('can_manage_card')->default(false);
                $table->boolean('see_their_cards')->default(true);
                $table->boolean('see_their_beneficiaries')->default(true);
                $table->boolean('can_add_beneficiary')->default(false);
                $table->boolean('can_manage_beneficiary')->default(false);
                $table->boolean('can_setup_2fa')->default(true);
                $table->boolean('can_change_trasnaction_pin')->default(true);
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
