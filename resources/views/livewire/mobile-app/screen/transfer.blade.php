<div class="screen transfer-screen" x-data="transferProcessor()">
    <!-- Header (Hide during processing and result) -->
    <div class="investment-header" x-show="$wire.step < 4">
        <button wire:click="backStep" class="btn-back"
            @if ($step === 1) onclick="window.history.back()" @endif>
            <i class="bi bi-arrow-left"></i>
        </button>
        <h1>Transfer Money</h1>
    </div>

    <!-- Progress Steps (Hide during processing and result) -->
    <div class="transfer-progress" x-show="$wire.step < 4">
        <div class="progress-step {{ $step >= 1 ? 'active' : '' }} {{ $step > 1 ? 'completed' : '' }}">
            <div class="step-number">1</div>
            <span>Details</span>
        </div>
        <div class="progress-line {{ $step > 1 ? 'active' : '' }}"></div>
        <div class="progress-step {{ $step >= 2 ? 'active' : '' }} {{ $step > 2 ? 'completed' : '' }}">
            <div class="step-number">2</div>
            <span>PIN</span>
        </div>
        @if (count($requiredVerifications) > 0)
            <div class="progress-line {{ $step > 2 ? 'active' : '' }}"></div>
            <div class="progress-step {{ $step >= 3 ? 'active' : '' }} {{ $step > 3 ? 'completed' : '' }}">
                <div class="step-number">3</div>
                <span>Verify</span>
            </div>
        @endif
    </div>

    <div class="transfer-container" x-show="$wire.step >= 1 && $wire.step <= 3">
        <!-- Step 1: Transfer Details -->
        @if ($step === 1)
            <div class="transfer-step">
                @if ($configuration->allow_international_transfers)
                    <!-- Transfer Type Toggle -->
                    <div class="transfer-type-toggle">
                        <button wire:click="$set('transferType', 'local')" @class(['type-btn', 'active' => $transferType === 'local'])>
                            Local Transfer
                        </button>
                        <button wire:click="$set('transferType', 'international')" @class(['type-btn', 'active' => $transferType === 'international'])>
                            International
                        </button>
                    </div>
                @endif
                <!-- Beneficiary Selection -->
                @if ($beneficiaries->count() > 0)
                    <div class="beneficiaries-section">
                        <h3 class="section-subtitle">
                            <i class="bi bi-people"></i>
                            Select Beneficiary
                        </h3>
                        <div class="beneficiaries-grid">
                            @foreach ($beneficiaries as $beneficiary)
                                <button wire:click="selectBeneficiary({{ $beneficiary->id }})"
                                    class="beneficiary-quick-btn">
                                    <div class="beneficiary-avatar">
                                        {{ strtoupper(substr($beneficiary->nickname ?: $beneficiary->account_name, 0, 2)) }}
                                    </div>
                                    <span
                                        class="beneficiary-name">{{ $beneficiary->nickname ?: $beneficiary->account_name }}</span>
                                    @if ($beneficiary->is_favorite)
                                        <i class="bi bi-star-fill favorite-icon"></i>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                        <div class="divider">
                            <span>OR</span>
                        </div>
                    </div>
                @endif

                <form wire:submit="proceedToPin">
                    <!-- Manual Entry -->
                    <div class="manual-entry-section">
                        <h3 class="section-subtitle">
                            <i class="bi bi-pencil"></i>
                            Enter Details Manually
                        </h3>

                        <!-- Select Bank -->
                        <div class="form-group">
                            <label class="form-label">Select Bank</label>
                            <select wire:model.live="bankId" class="form-select">
                                <option value="">Choose bank...</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                            @error('bankId')
                                <div class="form-error">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Account Number -->
                        <div class="form-group">
                            <label class="form-label">Account Number</label>
                            <input type="text" wire:model.live.debounce.500ms="accountNumber" class="form-control"
                                placeholder="Enter account number (min 7 digits)" maxlength="15">
                            @error('accountNumber')
                                <div class="form-error">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </div>
                            @enderror

                            @if ($accountName)
                                <div class="account-name-display {{ $accountFound ? 'found' : 'not-found' }}">
                                    <i class="bi bi-{{ $accountFound ? 'check-circle-fill' : 'x-circle-fill' }}"></i>
                                    {{ $accountName }}
                                </div>
                            @endif

                            @if (strlen($accountNumber) > 0 && strlen($accountNumber) < 7)
                                <div class="form-hint">
                                    <i class="bi bi-info-circle"></i>
                                    Enter at least 7 digits to search
                                </div>
                            @endif
                        </div>

                        <!-- Amount -->
                        <div class="form-group">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                {{-- <span class="input-group-text">$</span> --}}
                                <input type="number" wire:model="amount" class="form-control" placeholder="0.00"
                                    step="0.01" min="{{ $configuration->minimum_transfer }}">
                            </div>
                            @error('amount')
                                <div class="form-error">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- From Account -->
                        <div class="form-group">
                            <label class="form-label">From Account</label>
                            <select wire:model="sourceAccountId" class="form-select">
                                <option value="">Select source account...</option>
                                @foreach ($sourceAccounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->account_number }} - {{ $account->currency }}
                                        {{ number_format($account->balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sourceAccountId')
                                <div class="form-error">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label class="form-label">Description (Optional)</label>
                            <textarea wire:model="description" class="form-textarea" placeholder="Payment for..." rows="2" required></textarea>
                        </div>

                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="proceedToPin">
                                Continue
                                <i class="bi bi-arrow-right"></i>
                            </span>
                            <span wire:loading wire:target="proceedToPin">
                                <x-spinner />
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Step 2: PIN Verification -->
        @if ($step === 2)
            <div class="transfer-step">
                <div class="verification-card">
                    <i class="bi bi-shield-lock"></i>
                    <h3>Enter Transaction PIN</h3>
                    <p>Please enter your 5-digit transaction PIN to authorize this transfer</p>
                </div>

                <div class="transfer-summary">
                    <div class="summary-row">
                        <span>Recipient</span>
                        <strong>{{ $accountName }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Account</span>
                        <strong>{{ $accountNumber }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Bank</span>
                        <strong>{{ $banks->find($bankId)?->name }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Amount</span>
                        <strong class="amount-highlight">${{ number_format($amount, 2) }}</strong>
                    </div>
                    @if ($description)
                        <div class="summary-row">
                            <span>Description</span>
                            <strong>{{ $description }}</strong>
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">Transaction PIN</label>
                    <input type="password" wire:model="transactionPin" class="form-control text-center pin-input"
                        placeholder="•••••" maxlength="5" inputmode="numeric" wire:loading.attr="disabled"
                        autofocus>
                    {{-- @error('transactionPin')
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            {{ $message }}
                        </div>
                    @enderror --}}
                </div>

                <button wire:click="verifyPin" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="verifyPin">
                        Verify PIN
                        <i class="bi bi-check-circle"></i>
                    </span>
                    <span wire:loading wire:target="verifyPin">
                        <x-spinner />
                        Verifying...
                    </span>
                </button>
            </div>
        @endif

        <!-- Step 3: Verification Codes -->
        @if ($step === 3 && count($requiredVerifications) > 0)
            <div class="transfer-step">
                @php
                    $currentVerification = $requiredVerifications[$currentVerificationIndex];
                    $verificationType = $currentVerification['type'];
                @endphp

                <div class="verification-card">
                    <i class="bi bi-key-fill"></i>
                    <h3>{{ $verificationType->name }}</h3>
                    <p>{{ $verificationType->description }}</p>
                    <div class="verification-progress">
                        Step {{ $currentVerificationIndex + 1 }} of {{ count($requiredVerifications) }}
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Enter Verification Code</label>
                    <input type="text" wire:model="currentCodeInput" class="form-control text-center code-input"
                        placeholder="Enter code" autofocus>
                    @error('currentCodeInput')
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button wire:click="verifyCode" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="verifyCode">
                        {{ $currentVerificationIndex < count($requiredVerifications) - 1 ? 'Continue' : 'Complete Transfer' }}
                        <i class="bi bi-arrow-right"></i>
                    </span>
                    <span wire:loading wire:target="verifyCode">
                        <x-spinner />
                        Verifying...
                    </span>
                </button>
            </div>
        @endif
    </div>

    <!-- Step 4: Processing Animation -->
    <div x-show="isProcessing && $wire.step === 4" style="display: none;" x-transition class="processing-screen">
        <div class="processing-content">
            <div class="processing-icon">
                <div class="pulse-ring"></div>
                <div class="pulse-ring pulse-ring-delay"></div>
                <i class="bi bi-arrow-left-right"></i>
            </div>

            <h2>Processing Transfer</h2>
            <p class="processing-subtitle">Please wait while we process your transaction</p>

            <div class="processing-steps">
                <template x-for="(message, index) in messages" :key="index">
                    <div class="processing-step"
                        :class="{
                            'active': currentStep === index,
                            'completed': currentStep > index
                        }"
                        x-show="currentStep >= index" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform translate-x-4"
                        x-transition:enter-end="opacity-100 transform translate-x-0">
                        <div class="step-icon">
                            <i class="bi" :class="currentStep > index ? 'bi-check-circle-fill' : 'bi-arrow-repeat'"
                                :style="currentStep === index ? 'animation: spin 1s linear infinite;' : ''"></i>
                        </div>
                        <span x-text="message"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Step 5: Result Screen -->
    @if ($step === 5)
        <div class="result-screen" x-show="$wire.step === 5" x-transition>
            @if ($transferSuccess)
                <!-- Success Screen -->
                <div class="result-content success">
                    <div class="result-icon success-icon">
                        <div class="success-checkmark">
                            <div class="check-icon-transfer">
                                <i class="bi bi-check-circle text-success" style="font-size: 75px"></i>
                            </div>
                        </div>
                    </div>

                    <h2>Transfer Successful!</h2>
                    <p class="result-message">{{ $transferMessage }}</p>

                    <div class="result-details">
                        <div class="detail-row">
                            <span>Amount</span>
                            <strong>${{ number_format($amount, 2) }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Recipient</span>
                            <strong>{{ $accountName }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Account</span>
                            <strong>{{ $accountNumber }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Reference</span>
                            <strong>{{ $transferReference }}</strong>
                        </div>
                        <div class="detail-row">
                            <span>Date</span>
                            <strong>{{ now()->format('M d, Y • g:i A') }}</strong>
                        </div>
                    </div>

                    <div class="result-actions">
                        <a href="{{ route('dashboard') }}" class="btn-primary text-decoration-none">
                            <i class="bi bi-house-door"></i>
                            Back to Home
                        </a>
                        <button class="btn-secondary" onclick="window.print()">
                            <i class="bi bi-download"></i>
                            Download Receipt
                        </button>
                    </div>
                </div>
            @else
                <!-- Failure Screen -->
                <div class="result-content failure">
                    <div class="result-icon failure-icon">
                        <i class="bi bi-x-circle"></i>
                    </div>

                    <h2>Transfer Failed</h2>
                    <p class="result-message">{{ $transferMessage }}</p>

                    <div class="result-actions">
                        <button wire:click="resetTransfer" class="btn-primary">
                            <i class="bi bi-arrow-clockwise"></i>
                            Try Again
                        </button>
                        <button wire:click="resetTransfer" class="btn-secondary">
                            <i class="bi bi-house-door"></i>
                            Back to Home
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <livewire:mobile-app.component.bottom-nav />
</div>

@push('scripts')
    <script>
        function transferProcessor() {
            return {
                isProcessing: false,
                currentStep: 0,
                messages: [
                    'Connecting to secure server...',
                    'Verifying transaction details...',
                    'Authenticating transfer request...',
                    'Processing payment...',
                    'Transferring to recipient...',
                    'Finalizing transaction...'
                ],

                init() {
                    // Listen for transfer processing start
                    Livewire.on('start-transfer-processing', () => {
                        this.startProcessing();
                    });
                },

                startProcessing() {
                    this.isProcessing = true;
                    this.currentStep = 0;
                    this.animateSteps();
                },

                animateSteps() {
                    const stepDelay = 800; // 800ms per step

                    const interval = setInterval(() => {
                        if (this.currentStep < this.messages.length - 1) {
                            this.currentStep++;
                        } else {
                            clearInterval(interval);
                            // After all steps, execute the actual transfer
                            setTimeout(() => {
                                this.$wire.call('executeTransfer');
                                this.isProcessing = false;
                            }, 500);
                        }
                    }, stepDelay);
                }
            };
        }
    </script>
@endpush
