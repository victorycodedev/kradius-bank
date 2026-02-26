<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanGuarantor extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'name',
        'email',
        'phone_number',
        'relationship',
        'address',
        'occupation',
        'status',
        'consent_given',
        'consent_date',
    ];

    protected function casts(): array
    {
        return [
            'consent_given' => 'boolean',
            'consent_date' => 'datetime',
        ];
    }

    // Relationships
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
