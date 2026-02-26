<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_account_id',
        'card_number',
        'card_holder_name',
        'card_type',
        'card_brand',
        'cvv',
        'card_pin',
        'expiry_date',
        'daily_limit',
        'is_contactless_enabled',
        'card_status',
        'blocked_at',
        'block_reason',
    ];

    protected function casts(): array
    {
        return  [
            'expiry_date' => 'date',
            'is_contactless_enabled' => 'boolean',
            'blocked_at' => 'datetime',
        ];
    }


    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userAccount(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class);
    }

    public function cardTransactions(): HasMany
    {
        return $this->hasMany(CardTransaction::class);
    }

    // Helper methods
    public function isActive()
    {
        return $this->card_status === 'active';
    }

    public function isExpired()
    {
        return $this->expiry_date->isPast();
    }

    public function block($reason = null)
    {
        $this->card_status = 'blocked';
        $this->blocked_at = now();
        $this->block_reason = $reason;
        $this->save();
    }

    public function unblock()
    {
        $this->card_status = 'active';
        $this->blocked_at = null;
        $this->block_reason = null;
        $this->save();
    }
}
