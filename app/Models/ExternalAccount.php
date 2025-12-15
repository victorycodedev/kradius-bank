<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalAccount extends Model
{
    protected $fillable = [
        'bank_id',
        'account_name',
        'account_number',
        'account_type',
        'metadata',
    ];

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
