<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_applications_enabled',
        'max_loan_amount',
        'max_active_loans_per_user',
        'min_account_age_days',
        'min_account_balance',
        'require_guarantor',
        'min_guarantors',
        'required_documents',
        'terms_and_conditions',
    ];

    protected function casts(): array
    {
        return [
            'loan_applications_enabled' => 'boolean',
            'require_guarantor' => 'boolean',
            'required_documents' => 'array',
        ];
    }

    public static function get()
    {
        return static::first() ?? static::create([]);
    }
}
