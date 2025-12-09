<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Investment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'stock_id',
        'user_account_id',
        'reference_number',
        'amount',
        'shares',
        'purchase_price',
        'current_value',
        'profit_loss',
        'profit_loss_percentage',
        'total_profit_paid',
        'status',
        'investment_type',
        'duration_days',
        'roi_percentage',
        'maturity_date',
        'activated_at',
        'completed_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'maturity_date' => 'datetime',
            'activated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($investment) {
            if (empty($investment->reference_number)) {
                $investment->reference_number = 'INV' . strtoupper(Str::random(10));
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function userAccount(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class);
    }

    public function profits(): HasMany
    {
        return $this->hasMany(InvestmentProfit::class);
    }

    public function transactions(): hasMany
    {
        return $this->hasMany(InvestmentTransaction::class);
    }

    // Helper methods
    public function calculateCurrentValue(): void
    {
        $currentStockPrice = $this->stock->current_price;
        $this->current_value = $this->shares * $currentStockPrice;
        $this->profit_loss = $this->current_value - $this->amount;
        $this->profit_loss_percentage = ($this->profit_loss / $this->amount) * 100;
        $this->save();
    }

    public function isProfitable(): bool
    {
        return $this->profit_loss > 0;
    }

    public function isMatured(): bool
    {
        return $this->maturity_date && now()->isAfter($this->maturity_date);
    }

    public function activate(): void
    {
        $this->status = 'active';
        $this->activated_at = now();

        if ($this->duration_days) {
            $this->maturity_date = now()->addDays($this->duration_days);
        }

        $this->save();
    }

    public function complete(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();

        // Return investment + profit to user account
        $totalReturn = $this->current_value + $this->total_profit_paid;
        $this->userAccount->credit($totalReturn);

        // Create transaction record
        $this->transactions()->create([
            'type' => 'liquidation',
            'amount' => $totalReturn,
            'description' => 'Investment liquidation',
            'processed_by' => Auth::user()->id,
        ]);
    }

    public function addProfit($amount, $type = 'roi', $description = null, $isAuto = false): InvestmentProfit
    {
        $profit = $this->profits()->create([
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'status' => 'pending',
            'is_auto_generated' => $isAuto,
        ]);

        return $profit;
    }

    public function payProfit(InvestmentProfit $profit): void
    {
        if ($profit->status === 'paid') {
            return;
        }

        // Credit user account
        $this->userAccount->credit($profit->amount);

        // Update profit status
        $profit->update([
            'status' => 'paid',
            'paid_by' => Auth::user()->id,
            'paid_at' => now(),
        ]);

        // Update total profit paid
        $this->total_profit_paid += $profit->amount;
        $this->save();

        // Create transaction record
        $this->transactions()->create([
            'type' => 'profit',
            'amount' => $profit->amount,
            'description' => $profit->description ?? 'Investment profit payment',
            'processed_by' => Auth::user()->id,
        ]);
    }
}
