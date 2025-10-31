<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'reason',
        'description',
        'status',
        'resolution_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return  [
            'resolved_at' => 'datetime',
        ];
    }


    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function resolve($notes = null)
    {
        $this->status = 'resolved';
        $this->resolution_notes = $notes;
        $this->resolved_at = now();
        $this->save();
    }

    public function reject($notes = null)
    {
        $this->status = 'rejected';
        $this->resolution_notes = $notes;
        $this->resolved_at = now();
        $this->save();
    }
}