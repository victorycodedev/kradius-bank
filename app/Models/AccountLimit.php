<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountLimit extends Model
{
    use HasFactory;

    public $timestamps = false;


    protected $fillable = [
        'user_account_id',
        'daily_transfer_limit',
        'daily_withdrawal_limit',
        'single_transaction_limit',
    ];

    protected function casts(): array
    {
        return [
            'daily_transfer_limit' => 'decimal:2',
            'daily_withdrawal_limit' => 'decimal:2',
            'single_transaction_limit' => 'decimal:2',
            'updated_at' => 'datetime',
        ];
    }

    // Relationships
    public function userAccount(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class);
    }

    // Helper methods
    public function canTransfer($amount)
    {
        return $amount <= $this->daily_transfer_limit &&
            $amount <= $this->single_transaction_limit;
    }

    public function canWithdraw($amount)
    {
        return $amount <= $this->daily_withdrawal_limit &&
            $amount <= $this->single_transaction_limit;
    }
}