<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'name',
        'description',
        'category',
        'logo_url',
        'current_price',
        'opening_price',
        'previous_close',
        'day_high',
        'day_low',
        'price_change',
        'price_change_percentage',
        'minimum_investment',
        'maximum_investment',
        'is_active',
        'is_featured',
        'investment_count',
        'total_invested_amount',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'metadata' => 'array',
        ];
    }

    // Relationships
    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(StockPriceHistory::class);
    }

    // Helper methods
    public function updatePrice($newPrice, $adminId = null): void
    {
        $oldPrice = $this->current_price;
        $this->previous_close = $oldPrice;
        $this->current_price = $newPrice;
        $this->price_change = $newPrice - $oldPrice;
        $this->price_change_percentage = $oldPrice > 0 ? (($newPrice - $oldPrice) / $oldPrice) * 100 : 0;
        $this->save();

        // Record price history
        $this->priceHistories()->create([
            'price' => $newPrice,
            'closing_price' => $newPrice,
            'date' => now()->toDateString(),
            'updated_by' => $adminId,
        ]);

        // Update all active investments
        $this->updateInvestmentValues();
    }

    public function updateInvestmentValues(): void
    {
        $this->investments()->where('status', 'active')->each(function ($investment) {
            $investment->calculateCurrentValue();
        });
    }

    public function isPriceUp(): bool
    {
        return $this->price_change > 0;
    }

    public function isPriceDown(): bool
    {
        return $this->price_change < 0;
    }
}
