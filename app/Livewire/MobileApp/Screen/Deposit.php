<?php

namespace App\Livewire\MobileApp\Screen;

use App\Models\Setting;
use App\Models\Settings;
use App\Traits\HasAlerts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Livewire\Attributes\Title;
use Livewire\Component;

class Deposit extends Component
{
    use HasAlerts;

    public $depositMethod = 'crypto'; // crypto, bank
    public $selectedAccount = '';
    public $showDepositDetails = false;
    public $showSubmitModal = false;
    public $paymentDetails = [];
    public $currency = 'USD';

    // Submission form fields
    public $amount = '';
    public $proofOfPayment = '';
    public $transactionReference = '';
    public $paymentNote = '';

    #[Title('Deposit')]
    public function render()
    {
        $user = Auth::user();
        $settings = Settings::get();

        abort_if(!$settings->allow_deposits, 404);

        // Determine which payment details to use
        $useDefault = $user->use_default_deposit_details;

        // Get crypto details
        $cryptoEnabled = $useDefault
            ? $settings->enable_crypto_payment
            : $user->enable_crypto_payment;

        $cryptoDetails = $cryptoEnabled ? [
            'enabled' => true,
            'coin' => $useDefault ? $settings->coin : $user->coin,
            'crypto_name' => $useDefault ? $settings->crypto_name : $user->crypto_name,
            'network' => $useDefault ? $settings->network : $user->network,
            'wallet_address' => $useDefault ? $settings->wallet_address : $user->wallet_address,
            'more_attributes' => $useDefault ? $settings->more_crypto_attributes : $user->more_crypto_attributes,
        ] : ['enabled' => false];

        // Get bank details
        $bankEnabled = $useDefault
            ? $settings->enable_bank_payment
            : $user->enable_bank_payment;

        $bankDetails = $bankEnabled ? [
            'enabled' => true,
            'account_holder_name' => $useDefault ? $settings->account_holder_name : $user->account_holder_name,
            'bank_name' => $useDefault ? $settings->bank_name : $user->bank_name,
            'account_number' => $useDefault ? $settings->account_number : $user->account_number,
            'iban' => $useDefault ? $settings->iban : $user->iban,
            'swift' => $useDefault ? $settings->swift : $user->swift,
            'more_attributes' => $useDefault ? $settings->more_bank_attributes : $user->more_bank_attributes,
        ] : ['enabled' => false];

        // Set default deposit method based on enabled options
        if (!$cryptoEnabled && $bankEnabled) {
            $this->depositMethod = 'bank';
        }

        return view('livewire.mobile-app.screen.deposit', [
            'accounts' => $user->accounts,
            'cryptoDetails' => $cryptoDetails,
            'bankDetails' => $bankDetails,
        ]);
    }

    public function selectAccount($accountId)
    {
        $this->selectedAccount = $accountId;
        $this->showDepositDetails = true;
    }

    public function switchMethod($method)
    {
        $this->depositMethod = $method;
    }

    public function copyToClipboard($text)
    {
        $this->dispatch('copy-to-clipboard', text: $text);
        $this->successAlert('Copied to clipboard!');
    }

    public function closeDetails()
    {
        $this->showDepositDetails = false;
        $this->selectedAccount = '';
    }

    public function openSubmitModal()
    {
        $this->showSubmitModal = true;
        $this->resetValidation();
    }

    public function closeSubmitModal()
    {
        $this->dispatch('close-bottom-sheet', id: 'showSubmitModal');
        $this->amount = '';
        $this->proofOfPayment = '';
        $this->transactionReference = '';
        $this->paymentNote = '';
        $this->resetValidation();
    }

    public function submitDeposit()
    {
        $this->validate([
            'amount' => 'required|numeric|min:1',
            'transactionReference' => 'required|string|max:255',
            'proofOfPayment' => 'nullable|string|max:1000',
        ]);

        $account = Auth::user()->accounts()->findOrFail($this->selectedAccount);

        try {
            $transaction = $account->transactions()->create([
                'transaction_type' => 'deposit',
                'amount' => $this->amount,
                'currency' => $account->currency,
                'balance_before' => $account->balance,
                'balance_after' => $account->balance + $this->amount, // Will be updated when admin approves
                'reference_number' => 'DEP' . strtoupper(uniqid()),
                'description' => "Deposit via " . ($this->depositMethod === 'crypto' ? 'Cryptocurrency' : 'Bank Transfer'),
                'status' => 'pending_verification',
                'channel' => 'mobile_app',
                'metadata' => [
                    'payment_method' => $this->depositMethod,
                    'transaction_reference' => $this->transactionReference,
                    'proof_of_payment' => $this->proofOfPayment,
                    'payment_note' => $this->paymentNote,
                    'submitted_at' => now()->toDateTimeString(),
                ],
            ]);

            $this->successAlert('Deposit submitted successfully! We will verify and credit your account shortly.');
            $this->selectedAccount = '';
            $this->closeSubmitModal();
        } catch (\Exception $e) {
            $this->errorAlert('Failed to submit deposit. Please try again.');
            // \Log::error('Deposit submission error: ' . $e->getMessage());
        }
    }
}
