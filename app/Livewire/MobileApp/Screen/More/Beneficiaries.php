<?php

namespace App\Livewire\MobileApp\Screen\More;

use App\Traits\HasAlerts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;

class Beneficiaries extends Component
{
    use HasAlerts;

    public $showAddModal = false;
    public $showDeleteModal = false;
    public $selectedBeneficiary = null;
    public $deletePassword = '';

    // Add beneficiary form fields
    public $accountNumber = '';
    public $accountName = '';
    public $bankName = '';
    public $bankCode = '';
    public $nickname = '';
    public $isFavorite = false;

    public function mount(): void
    {
        abort_if(!Auth::user()->see_their_beneficiaries, 403);
    }

    #[Title('Beneficiaries')]
    public function render()
    {
        return view('livewire.mobile-app.screen.more.beneficiaries', [
            'beneficiaries' => Auth::user()->beneficiaries()->latest()->get(),
        ]);
    }

    public function openAddModal()
    {
        $this->showAddModal = true;
        $this->resetAddForm();
        $this->resetValidation();
    }

    public function closeAddModal()
    {
        $this->dispatch('close-bottom-sheet', id: 'showAddModal');
        $this->resetAddForm();
    }

    public function resetAddForm()
    {
        $this->accountNumber = '';
        $this->accountName = '';
        $this->bankName = '';
        $this->bankCode = '';
        $this->nickname = '';
        $this->isFavorite = false;
    }

    public function addBeneficiary()
    {
        $this->validate([
            'accountNumber' => 'required|string|max:50',
            'accountName' => 'required|string|max:255',
            'bankName' => 'required|string|max:255',
            'bankCode' => 'nullable|string|max:20',
            'nickname' => 'nullable|string|max:100',
        ]);

        try {
            Auth::user()->beneficiaries()->create([
                'account_number' => $this->accountNumber,
                'account_name' => $this->accountName,
                'bank_name' => $this->bankName,
                'bank_code' => $this->bankCode,
                'nickname' => $this->nickname ?: $this->accountName,
                'is_favorite' => $this->isFavorite,
            ]);
            $this->successAlert(message: 'Beneficiary added successfully!');
            $this->closeAddModal();
        } catch (\Exception $e) {
            $this->errorAlert(message: 'Failed to add beneficiary. Please try again.');
        }
    }

    public function toggleFavorite($beneficiaryId)
    {
        try {
            $beneficiary = Auth::user()->beneficiaries()->findOrFail($beneficiaryId);
            $beneficiary->update([
                'is_favorite' => !$beneficiary->is_favorite,
            ]);
            $this->successAlert(message: $beneficiary->is_favorite ? 'Added to favorites!' : 'Removed from favorites!');
        } catch (\Exception $e) {
            $this->errorAlert(message: 'Failed to update favorite status. Please try again.');
        }
    }

    public function selectBeneficiaryForDelete($beneficiaryId)
    {
        $this->selectedBeneficiary = Auth::user()->beneficiaries()->findOrFail($beneficiaryId);
        $this->showDeleteModal = true;
        $this->deletePassword = '';
        $this->resetValidation();
    }

    public function closeDeleteModal()
    {
        $this->dispatch('close-bottom-sheet', id: 'showDeleteModal');
        $this->selectedBeneficiary = null;
        $this->deletePassword = '';
    }

    public function deleteBeneficiary()
    {
        $this->validate([
            'deletePassword' => 'required',
        ]);

        if (!Hash::check($this->deletePassword, Auth::user()->password)) {
            $this->addError('deletePassword', 'Incorrect password.');
            return;
        }

        try {
            $this->selectedBeneficiary->delete();
            $this->successAlert(message: 'Beneficiary removed successfully!');
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->errorAlert(message: 'Failed to remove beneficiary. Please try again.');
        }
    }
}
