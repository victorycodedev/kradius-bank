<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InvestmentProfit extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_id',
        'reference_number',
        'amount',
        'type',
        'description',
        'status',
        'paid_by',
        'paid_at',
        'is_auto_generated',
    ];


    protected function casts(): array
    {
        return [
            'is_auto_generated' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($profit) {
            if (empty($profit->reference_number)) {
                $profit->reference_number = 'PFT' . strtoupper(Str::random(10));
            }
        });
    }

    // Relationships
    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // Helper methods
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
