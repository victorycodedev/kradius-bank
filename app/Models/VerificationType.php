<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerificationType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'amount',
        'is_percentage',
        'order',
        'is_active',
        'is_required',
        'applies_to',
        'threshold_amount',
    ];

    protected function casts(): array
    {
        return [
            'is_percentage' => 'boolean',
            'is_active' => 'boolean',
            'is_required' => 'boolean',
        ];
    }


    // Relationships
    public function userCodes(): HasMany
    {
        return $this->hasMany(UserVerificationCode::class);
    }

    public function transactionVerifications(): HasMany
    {
        return $this->hasMany(TransactionVerification::class);
    }

    // Helper methods
    public function appliesTo($transactionAmount, $transactionType = 'local')
    {
        if (!$this->is_active) {
            return false;
        }

        switch ($this->applies_to) {
            case 'above_threshold':
                return $this->threshold_amount && $transactionAmount >= $this->threshold_amount;
            case 'international':
                return $transactionType === 'international';
            case 'local':
                return $transactionType === 'local';
            case 'all':
            default:
                return true;
        }
    }
}
