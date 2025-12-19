<div class="screen security-screen">
    <!-- Header -->
    <div class="investment-header">
        <x-link :href="route('more')" class="btn-back" icon="arrow-left" />
        <h1>Security</h1>
    </div>

    <div class="security-container">
        <!-- Change Password Section -->
        <div class="security-section">
            <div class="section-header">
                <i class="bi bi-lock"></i>
                <h3>Change Password</h3>
            </div>

            <div class="form-field">
                <label class="form-label">Current Password</label>
                <input type="password" wire:model="currentPassword" class="form-input"
                    placeholder="Enter current password">
                @error('currentPassword')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-field">
                <label class="form-label">New Password</label>
                <input type="password" wire:model="newPassword" class="form-input" placeholder="Enter new password">
                @error('newPassword')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-field">
                <label class="form-label">Confirm New Password</label>
                <input type="password" wire:model="newPasswordConfirmation" class="form-input"
                    placeholder="Confirm new password">
            </div>

            <button wire:click="changePassword" class="btn-action" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="changePassword">
                    <i class="bi bi-check-circle"></i>
                    Change Password
                </span>
                <span wire:loading wire:target="changePassword">
                    <x-spinner />
                    Changing...
                </span>
            </button>
        </div>

        @if (Auth::user()->can_change_trasnaction_pin)
            <!-- Transaction PIN Section -->
            <div class="security-section">
                <div class="section-header">
                    <i class="bi bi-shield-lock"></i>
                    <h3>Transaction PIN</h3>
                </div>

                <p class="section-description">
                    {{ Auth::user()->pin ? 'Change your 5-digit PIN for secure transactions' : 'Set up a 5-digit PIN for secure transactions' }}
                </p>

                <button wire:click="openPinSetup" class="btn-action outline">
                    <i class="bi bi-key"></i>
                    {{ Auth::user()->pin ? 'Change PIN' : 'Setup PIN' }}
                </button>
            </div>
        @endif

        @if (Auth::user()->can_setup_2fa)
            <!-- Two-Factor Authentication Section -->
            <div class="security-section">
                <div class="section-header">
                    <i class="bi bi-shield-check"></i>
                    <h3>Two-Factor Authentication</h3>
                </div>

                <div class="twofa-status">
                    <div class="status-indicator {{ $twoFactorEnabled ? 'enabled' : 'disabled' }}">
                        <i class="bi bi-{{ $twoFactorEnabled ? 'check-circle-fill' : 'x-circle' }}"></i>
                        <span>{{ $twoFactorEnabled ? 'Enabled' : 'Disabled' }}</span>
                    </div>
                </div>

                <p class="section-description">
                    Add an extra layer of security by requiring a verification code when logging in.
                </p>

                @if ($twoFactorEnabled)
                    <button wire:click="disable2FA" class="btn-action danger" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="disable2FA">
                            <i class="bi bi-shield-slash"></i>
                            Disable 2FA
                        </span>
                        <span wire:loading wire:target="disable2FA">
                            <x-spinner />
                            Disabling...
                        </span>
                    </button>
                @else
                    <button wire:click="enable2FA" class="btn-action" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="enable2FA">
                            <i class="bi bi-shield-check"></i>
                            Enable 2FA
                        </span>
                        <span wire:loading wire:target="enable2FA">
                            <x-spinner />
                            Enabling...
                        </span>
                    </button>
                @endif

                <!-- Recovery Codes -->
                @if ($twoFactorEnabled && count($recoveryCodes) > 0)
                    <div class="recovery-codes-section">
                        <button wire:click="toggleRecoveryCodes" class="btn-recovery-toggle">
                            <i class="bi bi-key"></i>
                            {{ $showRecoveryCodes ? 'Hide' : 'View' }} Recovery Codes
                            <i class="bi bi-chevron-{{ $showRecoveryCodes ? 'up' : 'down' }}"></i>
                        </button>

                        @if ($showRecoveryCodes)
                            <div class="recovery-codes-box">
                                <p class="recovery-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Store these codes securely. Each code can only be used once.
                                </p>
                                <div class="codes-grid">
                                    @foreach ($recoveryCodes as $code)
                                        <div class="recovery-code">{{ $code }}</div>
                                    @endforeach
                                </div>
                                <button wire:click="regenerateRecoveryCodes" class="btn-regenerate">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    Regenerate Codes
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

    </div>

    <!-- PIN Setup Modal -->
    <x-bottom-sheet id="showPinSetup"
        title="{{ Auth::user()->pin ? 'Change Transaction PIN' : 'Setup Transaction PIN' }}">
        <div class="pin-setup-form">
            @if (Auth::user()->pin)
                <div class="form-field">
                    <label class="form-label">Current PIN</label>
                    <input type="password" wire:model="currentPin" class="form-input" placeholder="Enter current PIN"
                        maxlength="5" inputmode="numeric">
                    @error('currentPin')
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            @endif

            <div class="form-field">
                <label class="form-label">New PIN (5 digits)</label>
                <input type="password" wire:model="newPin" class="form-input" placeholder="Enter 5-digit PIN"
                    maxlength="5" inputmode="numeric">
                @error('newPin')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-field">
                <label class="form-label">Confirm New PIN</label>
                <input type="password" wire:model="newPinConfirmation" class="form-input"
                    placeholder="Confirm 5-digit PIN" maxlength="5" inputmode="numeric">
            </div>

            <div class="info-box">
                <i class="bi bi-info-circle"></i>
                <p>Your PIN will be required for transactions and sensitive operations.</p>
            </div>

            <div class="action-buttons gap-2">
                <button wire:click="closePinSetup" class="btn-cancel">
                    Cancel
                </button>
                <button wire:click="setupPin" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="setupPin">
                        <i class="bi bi-check-circle"></i>
                        {{ Auth::user()->pin ? 'Change PIN' : 'Setup PIN' }}
                    </span>
                    <span wire:loading wire:target="setupPin">
                        <x-spinner />
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </x-bottom-sheet>

    <!-- 2FA Setup Modal -->
    <x-bottom-sheet id="show2FAModal" title="Enable Two-Factor Authentication">
        <div class="twofa-setup-form">
            @if (!$showVerificationStep)
                <!-- QR Code Step -->
                <div class="twofa-instructions">
                    <p>Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>
                </div>

                @if ($qrCodeSvg)
                    <div class="qr-code-container">
                        {!! $qrCodeSvg !!}
                    </div>
                @endif

                @if ($manualSetupKey)
                    <div class="manual-setup">
                        <p class="manual-label">Or enter this code manually:</p>
                        <div class="setup-key">{{ $manualSetupKey }}</div>
                    </div>
                @endif

                <button wire:click="showVerification" class="btn-primary" wire:loading.attr="disabled">
                    <i class="bi bi-arrow-right"></i>
                    Continue
                </button>
            @else
                <!-- Verification Step -->
                <div class="twofa-instructions">
                    <p>Enter the 6-digit code from your authenticator app to verify setup</p>
                </div>

                <div class="form-field">
                    <label class="form-label">Verification Code</label>
                    <input type="text" wire:model="verificationCode" class="form-input text-center"
                        placeholder="000000" maxlength="6" inputmode="numeric">
                    @error('verificationCode')
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="action-buttons">
                    <button wire:click="close2FAModal" class="btn-cancel">
                        Cancel
                    </button>
                    <button wire:click="confirm2FA" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="confirm2FA">
                            <i class="bi bi-check-circle"></i>
                            Verify & Enable
                        </span>
                        <span wire:loading wire:target="confirm2FA">
                            <x-spinner />
                            Verifying...
                        </span>
                    </button>
                </div>
            @endif
        </div>
    </x-bottom-sheet>

    <livewire:mobile-app.component.bottom-nav />
</div>
