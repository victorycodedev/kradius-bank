<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_number',
        'account_type',
        'balance',
        'currency',
        'account_tier',
        'is_primary',
        'interest_rate',
        'minimum_balance',
        'frozen',
        'frozen_at',
        'frozen_reason',
        'status',
    ];


    protected function casts(): array
    {
        return  [
            'balance' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'minimum_balance' => 'decimal:2',
            'is_primary' => 'boolean',
            'frozen' => 'boolean',
            'frozen_at' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_account_id');
    }

    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'recipient_account_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function limits(): HasOne
    {
        return $this->hasOne(AccountLimit::class);
    }

    // Helper methods
    public function credit($amount)
    {
        $this->balance += $amount;
        $this->save();
        return $this;
    }

    public function debit($amount)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }
        $this->balance -= $amount;
        $this->save();
        return $this;
    }

    public function isFrozen()
    {
        return $this->frozen;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function hasMinimumBalance()
    {
        return $this->balance >= $this->minimum_balance;
    }
}