<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'price',
        'opening_price',
        'closing_price',
        'high',
        'low',
        'volume',
        'date',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }

    // Relationships
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
