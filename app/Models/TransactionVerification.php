<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionVerification extends Model
{
    protected $fillable = [
        'transaction_id',
        'verification_type_id',
        'step_order',
        'code_entered',
        'status',
        'attempts',
        'verified_at',
    ];

    protected function casts(): array
    {
        return  [
            'verified_at' => 'datetime',
        ];
    }

    // Relationships
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function verificationType(): BelongsTo
    {
        return $this->belongsTo(VerificationType::class);
    }

    // Helper methods
    public function verify($code)
    {
        $this->attempts++;
        $this->code_entered = $code;

        $userCode = UserVerificationCode::where('user_id', $this->transaction->userAccount->user_id)
            ->where('verification_type_id', $this->verification_type_id)
            ->where('code', $code)
            ->where('is_used', false)
            ->first();

        if ($userCode && $userCode->isValid()) {
            $this->status = 'verified';
            $this->verified_at = now();
            $userCode->markAsUsed();
            $this->save();
            return true;
        }

        $this->status = 'failed';
        $this->save();
        return false;
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isVerified()
    {
        return $this->status === 'verified';
    }
}