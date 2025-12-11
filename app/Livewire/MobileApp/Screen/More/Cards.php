<?php

namespace App\Livewire\MobileApp\Screen\More;

use App\Models\Card;
use App\Traits\HasAlerts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;

class Cards extends Component
{
    use HasAlerts;

    public $showAddCardModal = false;
    public $showDeleteModal = false;
    public $selectedCard = null;
    public $deletePassword = '';

    // Add card form fields
    public $cardHolderName = '';
    public $cardNumber = '';
    public $expiryDate = '';
    public $cvv = '';
    public $cardPin = '';
    public $userAccountId = '';
    public $cardType = 'debit';
    public $dailyLimit = 5000;

    #[Title('My Cards')]
    public function render()
    {
        return view('livewire.mobile-app.screen.more.cards', [
            'cards' => Auth::user()->cards()->with('userAccount')->latest()->get(),
            'accounts' => Auth::user()->accounts,
        ]);
    }

    public function openAddCardModal()
    {
        $this->showAddCardModal = true;
        $this->resetAddCardForm();
        $this->resetValidation();
    }

    public function closeAddCardModal()
    {
        // $this->showAddCardModal = false;
        $this->dispatch('close-bottom-sheet', id: 'showAddCardModal');
        $this->resetAddCardForm();
    }

    public function resetAddCardForm()
    {
        $this->cardHolderName = Auth::user()->name;
        $this->cardNumber = '';
        $this->expiryDate = '';
        $this->cvv = '';
        $this->cardPin = '';
        $this->userAccountId = '';
        $this->cardType = 'debit';
        $this->dailyLimit = 5000;
    }

    public function addCard()
    {
        $this->validate([
            'cardHolderName' => 'required|string|max:255',
            'cardNumber' => 'required|digits:16|unique:cards,card_number',
            'expiryDate' => 'required|date_format:Y-m|after:today',
            'cvv' => 'required|digits:3',
            'cardPin' => 'required|digits:4',
            'userAccountId' => 'required|exists:user_accounts,id',
            'cardType' => 'required|in:debit,credit',
            'dailyLimit' => 'required|numeric|min:100',
        ]);

        try {
            // Detect card brand from card number
            $cardBrand = $this->detectCardBrand($this->cardNumber);

            Card::create([
                'user_id' => Auth::id(),
                'user_account_id' => $this->userAccountId,
                'card_holder_name' => $this->cardHolderName,
                'card_number' => encrypt($this->cardNumber),
                'card_type' => $this->cardType,
                'card_brand' => $cardBrand,
                'cvv' => encrypt($this->cvv),
                'card_pin' => Hash::make($this->cardPin),
                'expiry_date' => $this->expiryDate . '-01',
                'daily_limit' => $this->dailyLimit,
                'is_contactless_enabled' => true,
                'card_status' => 'active',
            ]);

            $this->successAlert('Card added successfully!');
            $this->closeAddCardModal();
        } catch (\Exception $e) {
            $this->errorAlert(message: 'Failed to add card. Please try again.');
            // \Log::error('Add card error: ' . $e->getMessage());
        }
    }

    public function selectCardForDelete($cardId)
    {
        $this->selectedCard = Auth::user()->cards()->findOrFail($cardId);
        $this->showDeleteModal = true;
        $this->deletePassword = '';
        $this->resetValidation();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->dispatch('close-bottom-sheet', id: 'showDeleteModal');
        $this->selectedCard = null;
        $this->deletePassword = '';
    }

    public function deleteCard()
    {
        $this->validate([
            'deletePassword' => 'required',
        ]);

        if (!Hash::check($this->deletePassword, Auth::user()->password)) {
            $this->addError('deletePassword', 'Incorrect password.');
            return;
        }

        try {
            $this->selectedCard->delete();
            $this->successAlert(message: 'Card removed successfully!');
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->errorAlert(message: 'Failed to remove card. Please try again.');
            // \Log::error('Delete card error: ' . $e->getMessage());
        }
    }

    public function toggleCardStatus($cardId)
    {
        $card = Auth::user()->cards()->findOrFail($cardId);

        if ($card->isActive()) {
            $card->block('Blocked by user');
            $this->successAlert(message: 'Card blocked successfully!');
        } else {
            $card->unblock();
            $this->successAlert(message: 'Card activated successfully!');
        }
    }

    private function detectCardBrand($cardNumber)
    {
        $firstDigit = substr($cardNumber, 0, 1);
        $firstTwoDigits = substr($cardNumber, 0, 2);

        if ($firstDigit == '4') {
            return 'visa';
        } elseif (in_array($firstTwoDigits, ['51', '52', '53', '54', '55'])) {
            return 'mastercard';
        } elseif (in_array($firstTwoDigits, ['34', '37'])) {
            return 'amex';
        } elseif ($firstTwoDigits == '65' || substr($cardNumber, 0, 4) == '6011') {
            return 'discover';
        }

        return 'other';
    }

    public function maskCardNumber($cardNumber)
    {
        try {
            $decrypted = decrypt($cardNumber);
            return substr($decrypted, 0, 4) . ' **** **** ' . substr($decrypted, -4);
        } catch (\Exception $e) {
            return '**** **** **** ****';
        }
    }
}
