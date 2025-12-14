<?php

namespace App\Livewire\MobileApp\Screen\More;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;
use Livewire\Attributes\Title;
use Livewire\Component;

class Security extends Component
{
    // Password change
    public $currentPassword = '';
    public $newPassword = '';
    public $newPasswordConfirmation = '';

    // Transaction PIN
    public $currentPin = '';
    public $newPin = '';
    public $newPinConfirmation = '';
    public $showPinSetup = false;

    // 2FA
    public $twoFactorEnabled = false;
    public $show2FAModal = false;
    public $showVerificationStep = false;
    public $qrCodeSvg = '';
    public $manualSetupKey = '';
    public $verificationCode = '';
    public $recoveryCodes = [];
    public $showRecoveryCodes = false;

    #[Title('Security')]
    public function mount()
    {
        $user = Auth::user();
        $this->twoFactorEnabled = $user->hasEnabledTwoFactorAuthentication();
        $this->loadRecoveryCodes();
    }

    public function render()
    {
        return view('livewire.mobile-app.screen.more.security');
    }

    // Password Change Methods
    public function changePassword()
    {
        $this->validate([
            'currentPassword' => 'required|string|current_password',
            'newPassword' => ['required', 'string', PasswordRule::defaults(), 'confirmed:newPasswordConfirmation'],
        ]);

        try {
            Auth::user()->update([
                'password' => Hash::make($this->newPassword),
            ]);

            $this->reset('currentPassword', 'newPassword', 'newPasswordConfirmation');
            session()->flash('success', 'Password changed successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to change password. Please try again.');
        }
    }

    // Transaction PIN Methods
    public function openPinSetup()
    {
        $this->showPinSetup = true;
        $this->reset('currentPin', 'newPin', 'newPinConfirmation');
        $this->resetValidation();
    }

    public function closePinSetup()
    {
        $this->dispatch('close-bottom-sheet', id: 'showPinSetup');
        $this->reset('currentPin', 'newPin', 'newPinConfirmation');
    }

    public function setupPin()
    {
        $user = Auth::user();

        $rules = [
            'newPin' => 'required|digits:5|confirmed:newPinConfirmation',
        ];

        // If user already has a PIN, require current PIN
        if ($user->pin) {
            $rules['currentPin'] = 'required|digits:5';
        }

        $this->validate($rules);

        // Verify current PIN if exists
        if ($user->pin && !Hash::check($this->currentPin, $user->pin)) {
            $this->addError('currentPin', 'Current PIN is incorrect.');
            return;
        }

        try {
            $user->update([
                'pin' => Hash::make($this->newPin),
            ]);

            session()->flash('success', $user->pin ? 'Transaction PIN changed successfully!' : 'Transaction PIN set successfully!');
            $this->closePinSetup();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to set PIN. Please try again.');
        }
    }

    // 2FA Methods
    public function enable2FA(EnableTwoFactorAuthentication $enable)
    {
        if (!Features::enabled(Features::twoFactorAuthentication())) {
            session()->flash('error', '2FA is not enabled on this system.');
            return;
        }

        try {
            $enable(Auth::user());
            $this->load2FASetupData();
            $this->show2FAModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to enable 2FA. Please try again.');
        }
    }

    private function load2FASetupData()
    {
        $user = Auth::user();

        try {
            $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception $e) {
            $this->qrCodeSvg = '';
            $this->manualSetupKey = '';
        }
    }

    public function showVerification()
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm')) {
            $this->showVerificationStep = true;
            $this->resetValidation();
        } else {
            $this->close2FAModal();
            $this->twoFactorEnabled = true;
        }
    }

    public function confirm2FA(ConfirmTwoFactorAuthentication $confirm)
    {
        $this->validate([
            'verificationCode' => 'required|string|size:6',
        ]);

        try {
            $confirm(Auth::user(), $this->verificationCode);
            $this->twoFactorEnabled = true;
            $this->loadRecoveryCodes();
            session()->flash('success', '2FA enabled successfully!');
            $this->close2FAModal();
        } catch (\Exception $e) {
            $this->addError('verificationCode', 'Invalid verification code.');
        }
    }

    public function disable2FA(DisableTwoFactorAuthentication $disable)
    {
        try {
            $disable(Auth::user());
            $this->twoFactorEnabled = false;
            $this->recoveryCodes = [];
            session()->flash('success', '2FA disabled successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to disable 2FA. Please try again.');
        }
    }

    public function close2FAModal()
    {
        $this->dispatch('close-bottom-sheet', id: 'show2FAModal');
        $this->showVerificationStep = false;
        $this->reset('verificationCode', 'qrCodeSvg', 'manualSetupKey');
    }

    private function loadRecoveryCodes()
    {
        $user = Auth::user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception $e) {
                $this->recoveryCodes = [];
            }
        }
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate)
    {
        try {
            $generate(Auth::user());
            $this->loadRecoveryCodes();
            session()->flash('success', 'Recovery codes regenerated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to regenerate recovery codes.');
        }
    }

    public function toggleRecoveryCodes()
    {
        $this->showRecoveryCodes = !$this->showRecoveryCodes;
    }
}
