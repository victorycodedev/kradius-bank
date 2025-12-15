<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements FilamentUser, HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
        // 'pin',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'datetime',
            'biometric_enabled' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'pin' => 'encrypted',
            'use_default_deposit_details' => 'boolean',
            'more_bank_attributes' => 'array',
            'more_crypto_attributes' => 'array',
            'enable_crypto_payment' => 'boolean',
            'enable_bank_payment' => 'boolean',
            'transfer_success' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // public function setTransactionPinAttribute($value)
    // {
    //     $this->attributes['transaction_pin'] = Hash::make($value);
    // }

    public function verifyTransactionPin($pin)
    {
        return decrypt($this->pin) === $pin;
    }

    // Relationships
    public function accounts(): HasMany
    {
        return $this->hasMany(UserAccount::class);
    }

    public function primaryAccount(): HasOne
    {
        return $this->hasOne(UserAccount::class)->where('is_primary', true);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function securityQuestions(): HasMany
    {
        return $this->hasMany(SecurityQuestion::class);
    }

    public function billPayments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    public function verificationCodes(): HasMany
    {
        return $this->hasMany(UserVerificationCode::class, 'user_id');
    }

    // Helper methods
    public function isAccountLocked()
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function isKycVerified()
    {
        return $this->kyc_status === 'verified';
    }

    public function isActive()
    {
        return $this->account_status === 'active';
    }

    public function fullname(): Attribute
    {
        return Attribute::make(get: fn() => $this->first_name . ' ' . $this->last_name);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return !$this->hasRole('User');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function reviewedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'reviewed_by');
    }

    // In app/Models/User.php

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    public function activeInvestments(): HasMany
    {
        return $this->hasMany(Investment::class)->where('status', 'active');
    }

    public function totalInvestedAmount(): float
    {
        return $this->investments()->sum('amount');
    }

    public function totalProfitEarned(): float
    {
        return $this->investments()->sum('total_profit_paid');
    }

    // Helper methods for loan eligibility
    public function canApplyForLoan(): bool
    {
        $settings = LoanSetting::get();

        // Check if loan applications are enabled
        if (!$settings->loan_applications_enabled) {
            return false;
        }

        // Check account age
        $accountAgeDays = now()->diffInDays($this->created_at);
        if ($accountAgeDays < $settings->min_account_age_days) {
            return false;
        }

        // Check minimum balance
        $primaryAccount = $this->primaryAccount;
        if ($primaryAccount && $primaryAccount->balance < $settings->min_account_balance) {
            return false;
        }

        // Check maximum active loans
        $activeLoans = $this->loans()
            ->whereIn('status', ['approved', 'disbursed', 'active'])
            ->count();

        if ($activeLoans >= $settings->max_active_loans_per_user) {
            return false;
        }

        return true;
    }

    public function getActiveLoanCount(): int
    {
        return $this->loans()
            ->whereIn('status', ['approved', 'disbursed', 'active'])
            ->count();
    }

    public function getTotalLoanDebt(): float
    {
        return $this->loans()
            ->whereIn('status', ['disbursed', 'active'])
            ->sum('outstanding_balance');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
            ->singleFile();

        $this->addMediaCollection('kyc_documents');
    }
}
