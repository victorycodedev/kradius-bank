<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'min_amount',
        'max_amount',
        'interest_rate',
        'min_duration_months',
        'max_duration_months',
        'is_active',
        'requirements',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requirements' => 'array',
        ];
    }

    // Relationships
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function calculateMonthlyPayment($amount, $months)
    {
        $monthlyRate = ($this->interest_rate / 100) / 12;
        if ($monthlyRate == 0) {
            return $amount / $months;
        }
        return $amount * ($monthlyRate * pow(1 + $monthlyRate, $months)) / (pow(1 + $monthlyRate, $months) - 1);
    }
}
