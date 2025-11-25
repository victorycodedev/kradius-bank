<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class SecurityQuestion extends Model
{
    protected $fillable = [
        'user_id',
        'question',
        'answer',
    ];

    protected $hidden = [
        // 'answer',
    ];

    protected function casts(): array
    {
        return [
            'answer' => 'encrypted',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifyAnswer($answer)
    {
        return Hash::check(strtolower($answer), $this->answer);
    }
}
