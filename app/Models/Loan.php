<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'loan_type_id',
        'reference_number',
        'amount',
        'approved_amount',
        'interest_rate',
        'duration_months',
        'monthly_payment',
        'total_payable',
        'outstanding_balance',
        'status',
        'purpose',
        'employment_status',
        'monthly_income',
        'additional_info',
        'reviewed_by',
        'review_notes',
        'rejection_reason',
        'reviewed_at',
        'approved_at',
        'disbursed_at',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'disbursed_at' => 'datetime',
            'due_date' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($loan) {
            if (empty($loan->reference_number)) {
                $loan->reference_number = 'LN' . strtoupper(Str::random(10));
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loanType(): BelongsTo
    {
        return $this->belongsTo(LoanType::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LoanDocument::class);
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(LoanGuarantor::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LoanActivity::class);
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function calculatePaymentSchedule()
    {
        $monthlyPayment = $this->monthly_payment;
        $principal = $this->approved_amount ?? $this->amount;
        $monthlyRate = ($this->interest_rate / 100) / 12;

        $schedule = [];
        $balance = $principal;

        for ($i = 1; $i <= $this->duration_months; $i++) {
            $interestAmount = $balance * $monthlyRate;
            $principalAmount = $monthlyPayment - $interestAmount;
            $balance -= $principalAmount;

            $schedule[] = [
                'month' => $i,
                'payment' => $monthlyPayment,
                'principal' => $principalAmount,
                'interest' => $interestAmount,
                'balance' => max(0, $balance),
            ];
        }

        return $schedule;
    }

    public function logActivity($type, $description, $userId = null, $metadata = null): LoanActivity
    {
        return $this->activities()->create([
            'user_id' => $userId ?? Auth::user()->id,
            'activity_type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
