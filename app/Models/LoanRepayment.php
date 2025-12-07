<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'transaction_id',
        'reference_number',
        'amount',
        'principal_amount',
        'interest_amount',
        'due_date',
        'paid_at',
        'status',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($repayment) {
            if (empty($repayment->reference_number)) {
                $repayment->reference_number = 'RPY' . strtoupper(Str::random(10));
            }
        });
    }

    // Relationships
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // Helper methods
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || ($this->status === 'pending' && $this->due_date->isPast());
    }
}
