<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Settings extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dark_mode_enabled' => 'boolean',
            'charge_deposit_fee' => 'boolean',
            'allow_international_transfers' => 'boolean',
            'require_email_verification' => 'boolean',
            'require_phone_verification' => 'boolean',
            'require_2fa' => 'boolean',
            'force_2fa_for_withdrawals' => 'boolean',
            'auto_logout_on_idle' => 'boolean',
            'require_transaction_pin' => 'boolean',
            'kyc_required' => 'boolean',
            'require_selfie' => 'boolean',
            'require_id_upload' => 'boolean',
            'require_address_proof' => 'boolean',
            'loans_enabled' => 'boolean',
            'email_notifications_enabled' => 'boolean',
            'sms_notifications_enabled' => 'boolean',
            'push_notifications_enabled' => 'boolean',
            'notify_on_login' => 'boolean',
            'notify_on_transaction' => 'boolean',
            'notify_on_kyc_status' => 'boolean',
            'notify_on_loan_status' => 'boolean',
            'paystack_enabled' => 'boolean',
            'flutterwave_enabled' => 'boolean',
            'stripe_enabled' => 'boolean',
            'referral_enabled' => 'boolean',
            'savings_account_enabled' => 'boolean',
            'maintenance_mode' => 'boolean',
            'allow_registration' => 'boolean',
            'allow_transfers' => 'boolean',
            'allow_withdrawals' => 'boolean',
            'allow_deposits' => 'boolean',
            'demo_mode' => 'boolean',
            'google_analytics_enabled' => 'boolean',
            'facebook_pixel_enabled' => 'boolean',
            'api_enabled' => 'boolean',
            'api_requires_authentication' => 'boolean',
            'auto_backup_enabled' => 'boolean',
            'enable_crypto_payment' => 'boolean',
            'enable_bank_payment' => 'boolean',
            'transfer_success' => 'boolean',

            // JSON
            'allowed_id_types' => 'array',
            'more_bank_attributes' => 'array',
            'more_crypto_attributes' => 'array',

            // Timestamps
            'maintenance_scheduled_at' => 'datetime',
            'maintenance_ends_at' => 'datetime',

        ];
    }

    /**
     * Register media collections with optimizations
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('thumb')
                    ->fit(Fit::Contain, 150, 150)
                    ->format('webp')
                    ->quality(90)
                    ->performOnCollections('logo');

                $this
                    ->addMediaConversion('optimized')
                    ->fit(Fit::Contain, 500, 500)
                    ->format('webp')
                    ->quality(90)
                    ->performOnCollections('logo');
            });

        $this
            ->addMediaCollection('favicon')
            ->singleFile()
            ->acceptsMimeTypes(['image/x-icon', 'image/png', 'image/svg+xml'])
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('ico')
                    ->fit(Fit::Contain, 32, 32)
                    ->format('png')
                    ->performOnCollections('favicon');
            });

        $this
            ->addMediaCollection('login_banner')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('desktop')
                    ->fit(Fit::Contain, 1920, 1080)
                    ->format('webp')
                    ->quality(85)
                    ->performOnCollections('login_banner');

                $this
                    ->addMediaConversion('mobile')
                    ->fit(Fit::Contain, 768, 1024)
                    ->format('webp')
                    ->quality(85)
                    ->performOnCollections('login_banner');

                $this
                    ->addMediaConversion('thumb')
                    ->fit(Fit::Contain, 300, 200)
                    ->format('webp')
                    ->quality(80)
                    ->performOnCollections('login_banner');
            });
    }

    /**
     * Get settings instance (cached for performance)
     */
    public static function get(): self
    {
        return Cache::remember('app_settings', 3600, function () {
            return static::first() ?? static::create([
                'app_name' => config('app.name'),
            ]);
        });
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('app_settings');
    }

    /**
     * Boot method to clear cache on update
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    /**
     * Check if system is in maintenance mode
     */
    public function isInMaintenance(): bool
    {
        if (!$this->maintenance_mode) {
            return false;
        }

        // Check if scheduled maintenance has ended
        if ($this->maintenance_ends_at && now()->isAfter($this->maintenance_ends_at)) {
            $this->update(['maintenance_mode' => false]);
            return false;
        }

        return true;
    }

    /**
     * Calculate withdrawal fee
     */
    public function calculateWithdrawalFee(float $amount): float
    {
        if ($this->withdrawal_fee_type === 'percentage') {
            return ($amount * $this->withdrawal_fee_amount) / 100;
        }

        return $this->withdrawal_fee_amount;
    }

    /**
     * Calculate transfer fee
     */
    public function calculateTransferFee(float $amount): float
    {
        if ($this->transfer_fee_type === 'percentage') {
            return ($amount * $this->transfer_fee_amount) / 100;
        }

        return $this->transfer_fee_amount;
    }

    /**
     * Calculate deposit fee
     */
    public function calculateDepositFee(float $amount): float
    {
        if (!$this->charge_deposit_fee) {
            return 0;
        }

        if ($this->deposit_fee_type === 'percentage') {
            return ($amount * $this->deposit_fee_amount) / 100;
        }

        return $this->deposit_fee_amount;
    }

    /**
     * Check if amount is within deposit limits
     */
    public function isValidDepositAmount(float $amount): bool
    {
        return $amount >= $this->minimum_deposit && $amount <= $this->maximum_deposit;
    }

    /**
     * Check if amount is within withdrawal limits
     */
    public function isValidWithdrawalAmount(float $amount): bool
    {
        return $amount >= $this->minimum_withdrawal && $amount <= $this->maximum_withdrawal;
    }

    /**
     * Check if amount is within transfer limits
     */
    public function isValidTransferAmount(float $amount): bool
    {
        return $amount >= $this->minimum_transfer && $amount <= $this->maximum_transfer;
    }
}
