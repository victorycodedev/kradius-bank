<?php

namespace App\Livewire\MobileApp\Screen\More;

use App\Models\Settings;
use App\Notifications\AdminKycSubmitted;
use App\Traits\HasAlerts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

class Kyc extends Component
{
    use HasAlerts, WithFileUploads;

    public $kycStatus = '';
    public $documentType = '';
    public $documentNumber = '';
    public $documents = [];
    public $showSubmitModal = false;

    #[Title('KYC Verification')]
    public function mount()
    {
        $user = Auth::user();
        $this->kycStatus = $user->kyc_status;
        $this->documentType = $user->kyc_document_type ?? '';
        $this->documentNumber = $user->kyc_document_number ?? '';
    }

    public function render()
    {
        $user = Auth::user();
        $uploadedDocuments = $user->getMedia('kyc_documents');

        return view('livewire.mobile-app.screen.more.kyc', [
            'uploadedDocuments' => $uploadedDocuments,
        ]);
    }

    public function openSubmitModal()
    {
        if ($this->kycStatus === 'verified') {
            $this->successAlert(message: 'Your KYC is already verified.');
            return;
        }

        $this->showSubmitModal = true;
        $this->resetValidation();
    }

    public function closeSubmitModal()
    {
        $this->dispatch('close-bottom-sheet', id: 'showSubmitModal');
        $this->documents = [];
        $this->resetValidation();
    }

    public function submitKyc()
    {
        $this->validate([
            'documentType' => ['required', 'in:passport,national_id,drivers_license,voter_id'],
            'documentNumber' => ['required', 'string', 'max:50'],
            'documents' => ['required', 'array', 'min:1', 'max:5'],
            'documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        try {
            $user = Auth::user();

            // Update KYC information
            $user->update([
                'kyc_document_type' => $this->documentType,
                'kyc_document_number' => $this->documentNumber,
                'kyc_status' => 'pending',
            ]);

            // Upload documents
            foreach ($this->documents as $document) {
                $user->addMedia($document->getRealPath())
                    ->usingName($document->getClientOriginalName())
                    ->usingFileName($document->getClientOriginalName())
                    ->toMediaCollection('kyc_documents');
            }

            $settings = Settings::get();

            if ($settings->notify_on_kyc_status) {
                Notification::route('mail', $settings->notification_email)
                    ->notify(new AdminKycSubmitted($user));
            }

            $this->successAlert(message: 'KYC documents submitted successfully! We will review and notify you.');
            $this->closeSubmitModal();
            $this->kycStatus = 'pending';
        } catch (\Exception $e) {
            $this->errorAlert(message: 'Failed to submit KYC. Please try again.');
        }
    }

    public function deleteDocument($mediaId)
    {
        try {
            $user = Auth::user();
            $media = $user->getMedia('kyc_documents')->find($mediaId);

            if ($media) {
                $media->delete();
                $this->successAlert(message: 'Document deleted successfully!');
            }
        } catch (\Exception $e) {
            $this->errorAlert(message: 'Failed to delete document. Please try again.');
        }
    }
}
