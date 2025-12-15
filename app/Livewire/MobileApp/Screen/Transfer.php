<?php

namespace App\Livewire\MobileApp\Screen;

use App\Models\Bank;
use App\Models\ExternalAccount;
use App\Models\Settings;
use App\Models\UserAccount;
use App\Models\VerificationType;
use App\Traits\HasAlerts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;

class Transfer extends Component
{
    use HasAlerts;

    public $transferType = 'local'; // local, international
    public $step = 1; // 1: Details, 2: PIN, 3: Verification Codes, 4: Processing

    // Step 1: Transfer Details
    public $bankId = '';
    public $accountNumber = '';
    public $accountName = '';
    public $accountFound = false;
    public $recipientAccount = null;
    public $isExternalAccount = false;
    public $amount = '';
    public $sourceAccountId = '';
    public $description = '';

    // Step 2: PIN Verification
    public $transactionPin = '';

    // Step 3: Verification Codes
    public $verificationCodes = [];
    public $currentVerificationIndex = 0;
    public $currentCodeInput = '';
    public $requiredVerifications = [];

    #[Title('Transfers')]
    public function render()
    {
        return view('livewire.mobile-app.screen.transfer', [
            'banks' => Bank::where('active', true)->orderBy('name')->get(),
            'sourceAccounts' => Auth::user()->accounts()->get(),
        ]);
    }

    public function updatedAccountNumber($value)
    {
        $this->accountName = '';
        $this->accountFound = false;
        $this->recipientAccount = null;
        $this->isExternalAccount = false;

        if (strlen($value) >= 7 && strlen($value) <= 15) {
            $this->lookupAccount();
        }
    }

    public function lookupAccount()
    {
        if (!$this->accountNumber || !$this->bankId) {
            return;
        }

        // First, check internal accounts
        $userAccount = UserAccount::whereHas('user', function ($query) {
            $query->where('id', '!=', Auth::id()); // Exclude own accounts
        })
            ->where('account_number', $this->accountNumber)
            ->with('user')
            ->first();

        if ($userAccount) {
            $this->accountName = $userAccount->user->name;
            $this->accountFound = true;
            $this->recipientAccount = $userAccount;
            $this->isExternalAccount = false;
            return;
        }

        // Check external accounts
        $externalAccount = ExternalAccount::where('account_number', $this->accountNumber)
            ->where('bank_id', $this->bankId)
            ->first();

        if ($externalAccount) {
            $this->accountName = $externalAccount->account_name;
            $this->accountFound = true;
            $this->recipientAccount = $externalAccount;
            $this->isExternalAccount = true;
            return;
        }

        $this->accountName = 'Account not found';
    }

    public function proceedToPin()
    {
        $this->validate([
            'bankId' => 'required|exists:banks,id',
            'accountNumber' => 'required|string|min:7|max:15',
            'amount' => 'required|numeric|min:1',
            'sourceAccountId' => 'required|exists:user_accounts,id',
        ]);

        if (!$this->accountFound) {
            $this->errorAlert('Please enter a valid account number');
            return;
        }

        $sourceAccount = Auth::user()->accounts()->find($this->sourceAccountId);

        if ($sourceAccount->balance < $this->amount) {
            $this->errorAlert('Insufficient balance');
            return;
        }

        $this->step = 2;
    }

    public function verifyPin()
    {
        $this->validate([
            'transactionPin' => 'required|digits:5',
        ]);

        if (!Hash::check($this->transactionPin, Auth::user()->pin)) {
            $this->addError('transactionPin', 'Incorrect PIN');
            return;
        }

        // Load required verification codes
        $this->loadRequiredVerifications();

        if (count($this->requiredVerifications) > 0) {
            $this->step = 3;
        } else {
            $this->processTransfer();
        }
    }

    private function loadRequiredVerifications()
    {
        $verificationTypes = VerificationType::where('is_active', true)
            ->where('is_required', true)
            ->orderBy('order')
            ->get()
            ->filter(function ($type) {
                return $type->appliesTo($this->amount, $this->transferType);
            });

        foreach ($verificationTypes as $verificationType) {
            $userCode = Auth::user()->verificationCodes()
                ->where('verification_type_id', $verificationType->id)
                ->where('is_used', false)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($userCode) {
                $this->requiredVerifications[] = [
                    'type' => $verificationType,
                    'user_code' => $userCode,
                ];
            }
        }
    }

    public function verifyCode()
    {
        $this->validate([
            'currentCodeInput' => 'required|string',
        ]);

        $currentVerification = $this->requiredVerifications[$this->currentVerificationIndex];
        $userCode = $currentVerification['user_code'];

        if ($this->currentCodeInput !== $userCode->code) {
            $this->addError('currentCodeInput', 'Incorrect verification code');
            return;
        }

        // Mark as used
        $userCode->markAsUsed();

        // Move to next verification or process transfer
        $this->currentVerificationIndex++;
        $this->currentCodeInput = '';
        $this->resetErrorBag();

        if ($this->currentVerificationIndex >= count($this->requiredVerifications)) {
            $this->processTransfer();
        }
    }

    private function processTransfer()
    {
        $this->step = 4;

        // Check transfer success flags
        $user = Auth::user();
        $settings = Settings::get();

        $transferSuccess = $user->transfer_success ?? $settings->transfer_success ?? true;
        $failureMessage = $user->failed_transfer_message ?? $settings->failed_transfer_message ?? 'Transfer failed. Please try again.';

        if (!$transferSuccess) {
            $this->errorAlert($failureMessage);
            $this->resetTransfer();
            return;
        }

        DB::beginTransaction();
        try {
            $sourceAccount = Auth::user()->accounts()->findOrFail($this->sourceAccountId);

            // Create debit transaction for sender
            $debitTransaction = $sourceAccount->transactions()->create([
                'transaction_type' => 'transfer',
                'amount' => $this->amount,
                'currency' => $sourceAccount->currency,
                'balance_before' => $sourceAccount->balance,
                'balance_after' => $sourceAccount->balance - $this->amount,
                'reference_number' => 'TRF' . strtoupper(uniqid()),
                'description' => $this->description ?: "Transfer to {$this->accountName}",
                'recipient_account_id' => $this->isExternalAccount ? null : $this->recipientAccount->id,
                'status' => 'completed',
                'channel' => 'mobile_app',
                'completed_at' => now(),
                'metadata' => [
                    'transfer_type' => $this->transferType,
                    'recipient_account_number' => $this->accountNumber,
                    'recipient_name' => $this->accountName,
                    'bank_id' => $this->bankId,
                ],
            ]);

            // Deduct from sender
            $sourceAccount->decrement('balance', $this->amount);

            // If internal transfer, credit recipient
            if (!$this->isExternalAccount && $this->recipientAccount) {
                $this->recipientAccount->transactions()->create([
                    'transaction_type' => 'transfer',
                    'amount' => $this->amount,
                    'currency' => $this->recipientAccount->currency,
                    'balance_before' => $this->recipientAccount->balance,
                    'balance_after' => $this->recipientAccount->balance + $this->amount,
                    'reference_number' => 'TRF' . strtoupper(uniqid()),
                    'description' => "Transfer from {$user->name}",
                    'status' => 'completed',
                    'channel' => 'mobile_app',
                    'completed_at' => now(),
                ]);

                $this->recipientAccount->increment('balance', $this->amount);
            }

            DB::commit();

            $this->successAlert('Transfer successful!');
            $this->resetTransfer();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorAlert('Transfer failed. Please try again.');
            // \Log::error('Transfer error: ' . $e->getMessage());
            $this->resetTransfer();
        }
    }

    public function resetTransfer()
    {
        $this->reset([
            'step',
            'accountNumber',
            'accountName',
            'accountFound',
            'amount',
            'sourceAccountId',
            'description',
            'transactionPin',
            'currentCodeInput',
            'currentVerificationIndex',
            'requiredVerifications',
        ]);
    }

    public function backStep()
    {
        if ($this->step > 1) {
            $this->step--;
            $this->resetErrorBag();
        }
    }
}
