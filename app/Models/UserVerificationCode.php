<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVerificationCode extends Model
{
    protected $fillable = [
        'user_id',
        'verification_type_id',
        'code',
        'is_used',
        'used_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return  [
            'is_used' => 'boolean',
            'used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }


    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verificationType(): BelongsTo
    {
        return $this->belongsTo(VerificationType::class);
    }

    // Helper methods
    public function isValid()
    {
        return !$this->is_used &&
            (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function markAsUsed()
    {
        $this->is_used = true;
        $this->used_at = now();
        $this->save();
    }
}