<?php

namespace App\Traits;

use App\Models\User;
use App\Models\UserAccount;

trait CreatesUserAccount
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a default bank account for a user
     */
    protected function createDefaultAccount(User $user, array $options = []): UserAccount
    {
        $account = $user->accounts()->create([
            'account_number' => $this->generateAccountNumber(),
            'account_type' => $options['account_type'] ?? 'savings',
            'balance' => $options['balance'] ?? 0,
            'currency' => $options['currency'] ?? config('app.currency', 'NGN'),
            'account_tier' => $options['account_tier'] ?? 'basic',
            'is_primary' => $options['is_primary'] ?? true,
            'interest_rate' => $options['interest_rate'] ?? 2.5,
            'minimum_balance' => $options['minimum_balance'] ?? 0,
            'status' => 'active',
        ]);

        // Create account limits based on tier
        $this->createAccountLimits($account, $options['account_tier'] ?? 'basic');

        // Generate verification codes for the user
        // if (class_exists(\App\Services\TransactionVerificationService::class)) {
        //     $verificationService = app(\App\Services\TransactionVerificationService::class);
        //     $verificationService->generateUserCodes($user->id);
        // }

        return $account;
    }

    /**
     * Generate a unique account number
     */
    protected function generateAccountNumber(): string
    {
        $prefix = config('banking.account_prefix', '10'); // Configurable prefix

        do {
            $accountNumber = $prefix . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (UserAccount::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    /**
     * Create account limits based on tier
     */
    protected function createAccountLimits($account, $tier = 'basic'): void
    {
        $limits = match ($tier) {
            'gold' => [
                'daily_transfer_limit' => 5000000,
                'daily_withdrawal_limit' => 2000000,
                'single_transaction_limit' => 1000000,
            ],
            'premium' => [
                'daily_transfer_limit' => 2000000,
                'daily_withdrawal_limit' => 1000000,
                'single_transaction_limit' => 500000,
            ],
            default => [ // basic
                'daily_transfer_limit' => 500000,
                'daily_withdrawal_limit' => 200000,
                'single_transaction_limit' => 100000,
            ],
        };

        $account->limits()->create($limits);
    }

    /**
     * Upgrade account tier
     */
    protected function upgradeAccountTier($account, $newTier): void
    {
        $account->update(['account_tier' => $newTier]);

        // Update limits
        $account->limits()->delete();
        $this->createAccountLimits($account, $newTier);
    }
}
