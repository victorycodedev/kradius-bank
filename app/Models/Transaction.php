<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected $fillable = [
        'user_account_id',
        'transaction_type',
        'amount',
        'currency',
        'balance_before',
        'balance_after',
        'reference_number',
        'description',
        'recipient_account_id',
        'status',
        'channel',
        'metadata',
        'current_verification_step',
        'completed_at',
    ];


    protected function casts(): array
    {
        return  [
            'metadata' => 'array',
            'completed_at' => 'datetime',
        ];
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->reference_number)) {
                $transaction->reference_number = 'TXN' . strtoupper(Str::random(12));
            }
        });
    }

    // Relationships
    public function userAccount(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class);
    }

    public function recipientAccount(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'recipient_account_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(TransactionVerification::class)->orderBy('step_order');
    }

    public function cardTransaction(): HasOne
    {
        return $this->hasOne(CardTransaction::class);
    }

    public function billPayment(): HasOne
    {
        return $this->hasOne(BillPayment::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending' || $this->status === 'pending_verification';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function requiresVerification()
    {
        return $this->verifications()->where('status', 'pending')->exists();
    }

    public function getCurrentVerificationStep()
    {
        return $this->verifications()
            ->where('step_order', $this->current_verification_step)
            ->first();
    }

    public function advanceVerificationStep()
    {
        $this->current_verification_step++;
        $this->save();

        if (!$this->requiresVerification()) {
            $this->markAsCompleted();
        }
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    public function markAsFailed($reason = null)
    {
        $this->status = 'failed';
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['failure_reason'] = $reason;
            $this->metadata = $metadata;
        }
        $this->save();
    }
}