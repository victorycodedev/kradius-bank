<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InvestmentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'investments_enabled',
        'minimum_investment_amount',
        'maximum_investment_amount',
        'max_active_investments_per_user',
        'auto_profit_enabled',
        'auto_profit_frequency',
        'default_roi_percentage',
        'default_investment_duration_days',
        'early_withdrawal_penalty',
        'allow_partial_withdrawal',
        'require_kyc_for_investment',
        'min_account_age_days',
        'investment_terms',
    ];

    protected function casts(): array
    {
        return [
            'investments_enabled' => 'boolean',
            'auto_profit_enabled' => 'boolean',
            'allow_partial_withdrawal' => 'boolean',
            'require_kyc_for_investment' => 'boolean',
        ];
    }

    public static function get(): InvestmentSetting
    {
        return Cache::remember('investment_settings', 3600, function () {
            return static::first() ?? static::create([]);
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('investment_settings');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });
    }
}
