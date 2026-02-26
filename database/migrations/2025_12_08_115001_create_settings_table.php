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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // ✅ APP IDENTITY
            $table->string('app_name')->default('Banking App');
            $table->string('app_short_name')->nullable();
            $table->string('app_slogan')->nullable();
            $table->string('app_url')->nullable();
            $table->string('app_version')->default('1.0.0');
            $table->string('copyright_text')->nullable();

            // ✅ CONTACT & SUPPORT
            $table->string('support_email')->nullable();
            $table->string('notifiable_email')->nullable(); // For admin notifications
            $table->string('support_phone')->nullable();
            $table->string('support_whatsapp')->nullable();
            $table->text('support_address')->nullable();
            $table->string('support_working_hours')->nullable();

            // ✅ SOCIAL MEDIA
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();

            // ✅ BRANDING & APPEARANCE
            $table->string('primary_color')->default('#3b82f6');
            $table->string('secondary_color')->default('#8b5cf6');
            $table->string('accent_color')->default('#10b981');
            $table->boolean('dark_mode_enabled')->default(false);
            $table->string('font_family')->default('Inter');

            // ✅ FINANCIAL RULES - DEPOSITS
            $table->decimal('minimum_deposit', 15, 2)->default(100);
            $table->decimal('maximum_deposit', 15, 2)->default(1000000);
            $table->boolean('charge_deposit_fee')->default(false);
            $table->enum('deposit_fee_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('deposit_fee_amount', 15, 2)->default(0);

            // ✅ FINANCIAL RULES - WITHDRAWALS
            $table->decimal('minimum_withdrawal', 15, 2)->default(500);
            $table->decimal('maximum_withdrawal', 15, 2)->default(500000);
            $table->enum('withdrawal_fee_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('withdrawal_fee_amount', 15, 2)->default(50);
            $table->integer('withdrawal_processing_days')->default(1);

            // ✅ FINANCIAL RULES - TRANSFERS
            $table->decimal('minimum_transfer', 15, 2)->default(100);
            $table->decimal('maximum_transfer', 15, 2)->default(1000000);
            $table->enum('transfer_fee_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('transfer_fee_amount', 15, 2)->default(25);
            $table->boolean('allow_international_transfers')->default(false);

            // ✅ TRANSACTION LIMITS
            $table->decimal('daily_transaction_limit', 15, 2)->default(1000000);
            $table->decimal('monthly_transaction_limit', 15, 2)->default(10000000);
            $table->integer('max_transactions_per_day')->default(50);

            // ✅ SECURITY SETTINGS
            $table->boolean('require_email_verification')->default(true);
            $table->boolean('require_phone_verification')->default(false);
            $table->boolean('require_2fa')->default(false);
            $table->boolean('force_2fa_for_withdrawals')->default(false);
            $table->integer('session_timeout_minutes')->default(30);
            $table->integer('max_failed_login_attempts')->default(5);
            $table->integer('lockout_duration_minutes')->default(30);
            $table->boolean('auto_logout_on_idle')->default(true);
            $table->boolean('require_transaction_pin')->default(true);
            $table->integer('password_expiry_days')->default(90);

            // ✅ KYC & VERIFICATION
            $table->boolean('kyc_required')->default(true);
            $table->integer('min_kyc_level_for_withdrawal')->default(1);
            $table->integer('min_kyc_level_for_transfer')->default(1);
            $table->boolean('require_selfie')->default(true);
            $table->boolean('require_id_upload')->default(true);
            $table->boolean('require_address_proof')->default(false);
            $table->json('allowed_id_types')->nullable();
            $table->integer('kyc_expiry_months')->default(12); // Re-verify after X months

            // ✅ NOTIFICATION SETTINGS
            $table->boolean('email_notifications_enabled')->default(true);
            $table->boolean('sms_notifications_enabled')->default(false);
            $table->boolean('push_notifications_enabled')->default(true);
            $table->boolean('notify_on_login')->default(true);
            $table->boolean('notify_on_transaction')->default(true);
            $table->boolean('notify_on_kyc_status')->default(true);
            $table->boolean('notify_on_loan_status')->default(true);

            // ✅ EMAIL SETTINGS
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->string('smtp_password')->nullable();
            $table->string('smtp_encryption')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();

            // ✅ SMS SETTINGS
            $table->string('sms_provider')->nullable(); // twilio, nexmo, etc
            $table->string('sms_api_key')->nullable();
            $table->string('sms_api_secret')->nullable();
            $table->string('sms_sender_id')->nullable();

            // ✅ PAYMENT GATEWAY SETTINGS
            $table->boolean('paystack_enabled')->default(false);
            $table->string('paystack_public_key')->nullable();
            $table->string('paystack_secret_key')->nullable();
            $table->boolean('flutterwave_enabled')->default(false);
            $table->string('flutterwave_public_key')->nullable();
            $table->string('flutterwave_secret_key')->nullable();
            $table->boolean('stripe_enabled')->default(false);
            $table->string('stripe_public_key')->nullable();
            $table->string('stripe_secret_key')->nullable();

            // ✅ REFERRAL SYSTEM
            $table->boolean('referral_enabled')->default(false);
            $table->decimal('referral_bonus_amount', 15, 2)->default(0);
            $table->integer('referral_bonus_type')->default(1); // 1=fixed, 2=percentage
            $table->integer('min_referrals_for_bonus')->default(1);

            // ✅ INTEREST & SAVINGS
            $table->boolean('savings_account_enabled')->default(true);
            $table->decimal('savings_interest_rate', 5, 2)->default(2.5);
            $table->enum('interest_calculation_period', ['daily', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->decimal('minimum_balance_for_interest', 15, 2)->default(1000);

            // ✅ SYSTEM CONTROL
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            $table->timestamp('maintenance_scheduled_at')->nullable();
            $table->timestamp('maintenance_ends_at')->nullable();
            $table->boolean('allow_registration')->default(true);
            $table->boolean('allow_transfers')->default(true);
            $table->boolean('allow_withdrawals')->default(true);
            $table->boolean('allow_deposits')->default(true);
            $table->boolean('demo_mode')->default(false); // For testing

            // ✅ LEGAL & COMPLIANCE
            $table->text('terms_and_conditions')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->text('cookie_policy')->nullable();
            $table->text('refund_policy')->nullable();
            $table->string('company_registration_number')->nullable();
            $table->string('tax_identification_number')->nullable();

            // ✅ ANALYTICS & TRACKING
            $table->boolean('google_analytics_enabled')->default(false);
            $table->string('google_analytics_id')->nullable();
            $table->boolean('facebook_pixel_enabled')->default(false);
            $table->string('facebook_pixel_id')->nullable();

            // ✅ BACKUP & MAINTENANCE
            $table->boolean('auto_backup_enabled')->default(false);
            $table->enum('backup_frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->integer('backup_retention_days')->default(30);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
