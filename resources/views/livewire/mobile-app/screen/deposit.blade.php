<div class="screen deposit-screen" x-data="{
    depositMethod: $wire.entangle('depositMethod'),
    selectedAccount: $wire.entangle('selectedAccount'),
    'currency': $wire.entangle('currency'),
}">
    <!-- Header -->
    <div class="detail-header">
        <x-link :href="route('dashboard')" class="btn-back" icon="arrow-left" />
        <h1>Deposit Funds</h1>
    </div>

    <!-- Instructions Banner -->
    <div class="instructions-banner">
        <i class="bi bi-info-circle"></i>
        <div>
            <h4>How to Deposit</h4>
            <p>Select your account, choose payment method, and follow the instructions to deposit funds.</p>
        </div>
    </div>

    <!-- Select Account Section -->
    <div class="deposit-section">
        <h3 class="section-title">Select Account to Deposit To</h3>
        <div class="accounts-grid">
            @forelse($accounts as $account)
                <div class="account-card" @click="currency = '{{ $account->currency }}'"
                    wire:click="selectAccount({{ $account->id }})" @class(['selected' => $selectedAccount == $account->id])>
                    <div class="account-card-header">
                        <div class="account-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div class="account-info">
                            <h4>{{ ucfirst(Str::replace('_', ' ', $account->account_type)) }} Account</h4>
                            <p>{{ $account->account_number }}</p>
                        </div>
                        @if ($selectedAccount == $account->id)
                            <i class="bi bi-check-circle-fill check-icon"></i>
                        @endif
                    </div>
                    <div class="account-balance">
                        <span class="balance-label">Balance</span>
                        <span class="balance-amount">
                            {{ $account->currency }} {{ number_format($account->balance, 2) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="empty-state-small">
                    <i class="bi bi-wallet-fill"></i>
                    <p>No accounts available</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Payment Method Selection -->
    @if ($selectedAccount)
        <div class="deposit-section">
            <h3 class="section-title">Select Payment Method</h3>
            <div class="payment-methods">
                @if ($cryptoDetails['enabled'])
                    <button @click="depositMethod = 'crypto'"
                        :class="{ 'method-card': true, 'active': depositMethod === 'crypto' }">
                        <div class="method-icon crypto">
                            <i class="bi bi-currency-bitcoin"></i>
                        </div>
                        <div class="method-info">
                            <h4>Cryptocurrency</h4>
                            <p>{{ $cryptoDetails['coin'] ?? 'Crypto' }} ({{ $cryptoDetails['network'] ?? 'Network' }})
                            </p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                @endif

                @if ($bankDetails['enabled'])
                    <button @click="depositMethod = 'bank'"
                        :class="{ 'method-card': true, 'active': depositMethod === 'bank' }">
                        <div class="method-icon bank">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div class="method-info">
                            <h4>Bank Transfer</h4>
                            <p>{{ $bankDetails['bank_name'] ?? 'Wire Transfer' }}</p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                @endif

                @if (!$cryptoDetails['enabled'] && !$bankDetails['enabled'])
                    <div class="empty-state-small">
                        <i class="bi bi-exclamation-triangle"></i>
                        <p>No payment methods available. Please contact support.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment Details -->
        <div class="deposit-section">
            <!-- Crypto Details -->
            <div x-show="depositMethod === 'crypto'" x-transition>
                @if ($cryptoDetails['enabled'])
                    <div class="payment-details-card">
                        <div class="details-header">
                            <i class="bi bi-currency-bitcoin"></i>
                            <h3>Cryptocurrency Details</h3>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Coin</span>
                            <div class="detail-value-with-copy">
                                <span class="detail-value">{{ $cryptoDetails['crypto_name'] }}
                                    ({{ $cryptoDetails['coin'] }})</span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Network</span>
                            <div class="detail-value-with-copy">
                                <span class="detail-value">{{ $cryptoDetails['network'] }}</span>
                            </div>
                        </div>

                        <div class="detail-item highlight">
                            <span class="detail-label">Wallet Address</span>
                            <div class="detail-value-with-copy">
                                <span class="detail-value mono">{{ $cryptoDetails['wallet_address'] }}</span>
                                <button wire:click="copyToClipboard('{{ $cryptoDetails['wallet_address'] }}')"
                                    class="copy-btn" wire:loading.attr="disabled">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        @if ($cryptoDetails['more_attributes'])
                            @foreach ($cryptoDetails['more_attributes'] as $key => $value)
                                <div class="detail-item">
                                    <span class="detail-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                    <div class="detail-value-with-copy">
                                        <span class="detail-value">{{ $value }}</span>
                                        <button wire:click="copyToClipboard('{{ $value }}')" class="copy-btn"
                                            wire:loading.attr="disabled">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <div class="warning-box">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>
                                <strong>Important Notice:</strong>
                                <p>Only send {{ $cryptoDetails['coin'] }} to this address via
                                    {{ $cryptoDetails['network'] }} network. Sending any other coin or using a
                                    different network will result in permanent loss of funds.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Bank Details -->
            <div x-show="depositMethod === 'bank'" x-transition>
                @if ($bankDetails['enabled'])
                    <div class="payment-details-card">
                        <div class="details-header">
                            <i class="bi bi-bank"></i>
                            <h3>Bank Transfer Details</h3>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Account Holder Name</span>
                            <div class="detail-value-with-copy">
                                <span class="detail-value">{{ $bankDetails['account_holder_name'] }}</span>
                                <button wire:click="copyToClipboard('{{ $bankDetails['account_holder_name'] }}')"
                                    class="copy-btn" wire:loading.attr="disabled">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Bank Name</span>
                            <div class="detail-value-with-copy">
                                <span class="detail-value">{{ $bankDetails['bank_name'] }}</span>
                            </div>
                        </div>

                        <div class="detail-item highlight">
                            <span class="detail-label">Account Number</span>
                            <div class="detail-value-with-copy">
                                <span class="detail-value mono">{{ $bankDetails['account_number'] }}</span>
                                <button wire:click="copyToClipboard('{{ $bankDetails['account_number'] }}')"
                                    class="copy-btn" wire:loading.attr="disabled">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        @if ($bankDetails['iban'])
                            <div class="detail-item">
                                <span class="detail-label">IBAN</span>
                                <div class="detail-value-with-copy">
                                    <span class="detail-value mono">{{ $bankDetails['iban'] }}</span>
                                    <button wire:click="copyToClipboard('{{ $bankDetails['iban'] }}')"
                                        class="copy-btn" wire:loading.attr="disabled">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if ($bankDetails['swift'])
                            <div class="detail-item">
                                <span class="detail-label">SWIFT/BIC Code</span>
                                <div class="detail-value-with-copy">
                                    <span class="detail-value mono">{{ $bankDetails['swift'] }}</span>
                                    <button wire:click="copyToClipboard('{{ $bankDetails['swift'] }}')"
                                        class="copy-btn" wire:loading.attr="disabled">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if ($bankDetails['more_attributes'])
                            @foreach ($bankDetails['more_attributes'] as $key => $value)
                                <div class="detail-item">
                                    <span class="detail-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                    <div class="detail-value-with-copy">
                                        <span class="detail-value">{{ $value }}</span>
                                        <button wire:click="copyToClipboard('{{ $value }}')" class="copy-btn"
                                            wire:loading.attr="disabled">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <div class="info-box">
                            <i class="bi bi-info-circle-fill"></i>
                            <div>
                                <strong>Processing Time:</strong>
                                <p>Bank transfers typically take 1-3 business days to process. Please ensure you
                                    reference your account number in the transfer description.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Next Steps -->
        <div class="deposit-section">
            <div class="next-steps-card">
                <h4>What happens next?</h4>
                <ol class="steps-list">
                    <li>
                        <i class="bi bi-1-circle-fill"></i>
                        <span>Copy the payment details above</span>
                    </li>
                    <li>
                        <i class="bi bi-2-circle-fill"></i>
                        <span>Make the transfer from your wallet/bank</span>
                    </li>
                    <li>
                        <i class="bi bi-3-circle-fill"></i>
                        <span>Your account will be credited once the payment is confirmed</span>
                    </li>
                    <li>
                        <i class="bi bi-4-circle-fill"></i>
                        <span>You'll receive a notification when funds are available</span>
                    </li>
                </ol>
            </div>
        </div>

        <!-- Submit Deposit Button -->
        <div class="deposit-section">
            <button wire:click="openSubmitModal" class="btn-submit-deposit">
                <i class="bi bi-check-circle"></i>
                Submit Deposit Proof
            </button>
        </div>
    @endif

    <!-- Submit Deposit Modal -->
    <x-bottom-sheet id="showSubmitModal" title="Submit Deposit">
        <div class="submit-deposit-form">
            <div class="info-box">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    <strong>Important:</strong>
                    <p>Please complete your payment first, then submit the details below for verification.</p>
                </div>
            </div>

            <!-- Amount -->
            <div class="form-field">
                <label class="form-label">Deposit Amount *</label>
                <div class="input-group">
                    <span class="input-group-text" x-text="currency"></span>
                    <input type="number" wire:model="amount" class="form-control" placeholder="0.00"
                        step="0.01">
                </div>
                @error('amount')
                    <div class="text-danger text-sm">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Transaction Reference -->
            <div class="form-field">
                <label class="form-label">Transaction Reference/Hash *</label>
                <input type="text" wire:model="transactionReference" class="form-control"
                    placeholder="Enter your transaction reference or hash">
                <small class="form-hint">
                    {{ $depositMethod === 'crypto' ? 'Transaction hash from your wallet' : 'Bank transfer reference number' }}
                </small>
                @error('transactionReference')
                    <div class="text-danger text-sm">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Proof of Payment -->
            <div class="form-field">
                <label class="form-label">Proof of Payment (Optional)</label>
                <textarea wire:model="proofOfPayment" class="form-textarea"
                    placeholder="Paste screenshot URL or additional payment details..." rows="3"></textarea>
                <small class="form-hint">
                    Upload your payment screenshot to an image hosting service and paste the URL here
                </small>
                @error('proofOfPayment')
                    <div class="text-danger text-sm">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Payment Note -->
            <div class="form-field">
                <label class="form-label">Additional Notes (Optional)</label>
                <textarea wire:model="paymentNote" class="form-textarea"
                    placeholder="Any additional information about this deposit..." rows="2"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons gap-2">
                <button wire:click="closeSubmitModal" class="btn-cancel">
                    Cancel
                </button>
                <button wire:click="submitDeposit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submitDeposit">
                        <i class="bi bi-check-circle"></i>
                        Submit for Verification
                    </span>
                    <span wire:loading wire:target="submitDeposit">
                        <x-spinner />
                        Submitting...
                    </span>
                </button>
            </div>
        </div>
    </x-bottom-sheet>

    <livewire:mobile-app.component.bottom-nav />
</div>

<!-- Copy to Clipboard Script -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('copy-to-clipboard', (event) => {
            const text = event.text;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    console.log('Copied to clipboard');
                }).catch(err => {
                    console.error('Failed to copy:', err);
                    fallbackCopy(text);
                });
            } else {
                fallbackCopy(text);
            }
        });
    });

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            console.log('Fallback copy successful');
        } catch (err) {
            console.error('Fallback copy failed:', err);
        }
        document.body.removeChild(textarea);
    }
</script>
