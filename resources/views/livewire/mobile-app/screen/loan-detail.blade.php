<div class="screen loan-detail-screen">
    <div class="loan-header">
        <x-link :href="route('loans')" class="btn-back" icon="arrow-left" />
        <h1>Loan Details</h1>
    </div>

    <!-- Loan Overview Card -->
    <div class="loan-overview-card">
        <div class="loan-type-header">
            <div class="loan-type-badge">
                {{ $loan->loanType->name }}
            </div>
            <span class="loan-status status-{{ $loan->status }}">
                {{ ucfirst($loan->status) }}
            </span>
        </div>

        <div class="loan-amount-display">
            <span class="label">Loan Amount</span>
            <h2>${{ number_format($loan->amount, 2) }}</h2>
        </div>

        @if ($loan->isActive() || $loan->status === 'approved')
            <div class="outstanding-section">
                <div class="outstanding-amount">
                    <span class="label">Outstanding Balance</span>
                    <h3>${{ number_format($loan->outstanding_balance, 2) }}</h3>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar">
                        <div class="progress-fill"
                            style="width: {{ (($loan->total_payable - $loan->outstanding_balance) / $loan->total_payable) * 100 }}%">
                        </div>
                    </div>
                    <span class="progress-text">
                        {{ number_format((($loan->total_payable - $loan->outstanding_balance) / $loan->total_payable) * 100, 1) }}%
                        paid
                    </span>
                </div>
            </div>
        @endif

        <div class="loan-info-grid">
            <div class="info-item">
                <i class="bi bi-percent"></i>
                <div>
                    <span class="info-label">Interest Rate</span>
                    <span class="info-value">{{ $loan->interest_rate }}% p.a.</span>
                </div>
            </div>
            <div class="info-item">
                <i class="bi bi-calendar"></i>
                <div>
                    <span class="info-label">Duration</span>
                    <span class="info-value">{{ $loan->duration_months }} months</span>
                </div>
            </div>
            <div class="info-item">
                <i class="bi bi-calendar-check"></i>
                <div>
                    <span class="info-label">Monthly Payment</span>
                    <span class="info-value">${{ number_format($loan->monthly_payment, 2) }}</span>
                </div>
            </div>
            <div class="info-item">
                <i class="bi bi-cash"></i>
                <div>
                    <span class="info-label">Total Payable</span>
                    <span class="info-value">${{ number_format($loan->total_payable, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="loan-reference">
            <i class="bi bi-hash"></i>
            {{ $loan->reference_number }}
            <span class="loan-date">
                <i class="bi bi-clock"></i>
                Applied {{ $loan->created_at->format('M j, Y') }}
            </span>
        </div>

        @if ($loan->isActive() && $loan->outstanding_balance > 0)
            <button wire:click="openPayFullModal" class="btn-pay-full">
                <i class="bi bi-cash-stack"></i>
                Pay Full Outstanding (${{ number_format($loan->outstanding_balance, 2) }})
            </button>
        @endif
    </div>

    <!-- Repayment Statistics -->
    <div class="repayment-stats">
        <div class="stat-box">
            <i class="bi bi-check-circle stat-icon success"></i>
            <div>
                <span class="stat-value">{{ $paidCount }}</span>
                <span class="stat-label">Paid</span>
            </div>
        </div>
        <div class="stat-box">
            <i class="bi bi-clock-history stat-icon pending"></i>
            <div>
                <span class="stat-value">{{ $pendingCount }}</span>
                <span class="stat-label">Pending</span>
            </div>
        </div>
        <div class="stat-box">
            <i class="bi bi-exclamation-triangle stat-icon danger"></i>
            <div>
                <span class="stat-value">{{ $overdueCount }}</span>
                <span class="stat-label">Overdue</span>
            </div>
        </div>
    </div>

    <!-- Repayment Schedule -->
    <div class="repayment-section pb-4">
        <h3 class="section-title">Repayment Schedule</h3>

        <div class="repayment-list">
            @forelse($repayments as $repayment)
                <div class="repayment-card {{ $repayment->isPaid() ? 'paid' : ($repayment->isOverdue() ? 'overdue' : '') }}"
                    wire:key="repayment-{{ $repayment->id }}">
                    <div class="repayment-header">
                        <div class="repayment-status-icon">
                            @if ($repayment->isPaid())
                                <i class="bi bi-check-circle-fill"></i>
                            @elseif($repayment->isOverdue())
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            @else
                                <i class="bi bi-clock"></i>
                            @endif
                        </div>
                        <div class="repayment-info">
                            <h4>{{ $repayment->reference_number }}</h4>
                            <p>
                                @if ($repayment->isPaid())
                                    Paid on {{ $repayment->paid_at->format('M j, Y') }}
                                @else
                                    Due {{ $repayment->due_date->format('M j, Y') }}
                                    @if ($repayment->isOverdue())
                                        <span class="overdue-badge">Overdue</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                        <div class="repayment-amount">
                            <span class="amount">${{ number_format($repayment->amount, 2) }}</span>
                        </div>
                    </div>

                    <div class="repayment-breakdown">
                        <div class="breakdown-item">
                            <span>Principal</span>
                            <strong>${{ number_format($repayment->principal_amount, 2) }}</strong>
                        </div>
                        <div class="breakdown-item">
                            <span>Interest</span>
                            <strong>${{ number_format($repayment->interest_amount, 2) }}</strong>
                        </div>
                    </div>

                    @if (!$repayment->isPaid() && ($loan->isActive() || $loan->status === 'approved'))
                        <button wire:click="selectRepayment({{ $repayment->id }})" class="btn-pay-repayment"
                            wire:loading.attr="disabled">
                            <i class="bi bi-credit-card"></i>
                            Pay Now
                        </button>
                    @endif

                    @if ($repayment->isPaid() && $repayment->payment_method)
                        <div class="payment-method">
                            <i class="bi bi-info-circle"></i>
                            Payment method: {{ ucfirst(str_replace('_', ' ', $repayment->payment_method)) }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state-small">
                    <i class="bi bi-calendar-x"></i>
                    <p>No repayment schedule created yet</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Payment Modal -->
    <x-bottom-sheet id="showPaymentModal" title="Make Payment">
        <div class="payment-form">
            <!-- Payment Summary -->
            @if ($selectedRepayment)
                <div class="payment-summary-card">
                    <h4>Payment Details</h4>
                    <div class="summary-row">
                        <span>Reference</span>
                        <strong>{{ $selectedRepayment->reference_number }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Due Date</span>
                        <strong>{{ $selectedRepayment->due_date->format('M j, Y') }}</strong>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row">
                        <span>Principal Amount</span>
                        <strong>${{ number_format($selectedRepayment->principal_amount, 2) }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Interest Amount</span>
                        <strong>${{ number_format($selectedRepayment->interest_amount, 2) }}</strong>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row total">
                        <span>Total Payment</span>
                        <strong>${{ number_format($selectedRepayment->amount, 2) }}</strong>
                    </div>
                </div>
            @else
                <div class="payment-summary-card">
                    <h4>Pay Full Outstanding</h4>
                    <div class="summary-row">
                        <span>Loan Reference</span>
                        <strong>{{ $loan->reference_number }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Remaining Payments</span>
                        <strong>{{ $pendingCount + $overdueCount }}</strong>
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row total">
                        <span>Total Outstanding</span>
                        <strong>${{ number_format($loan->outstanding_balance, 2) }}</strong>
                    </div>
                </div>
            @endif

            <!-- Select Account -->
            <div class="form-field">
                <label class="form-label">Select Account</label>
                <select wire:model="selectedAccount" class="form-select">
                    <option value="">Choose account...</option>
                    @foreach (Auth::user()->accounts as $account)
                        <option value="{{ $account->id }}">
                            {{ $account->account_number }}
                            ({{ $account->currency }} {{ number_format($account->balance, 2) }})
                        </option>
                    @endforeach
                </select>
                @error('selectedAccount')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Payment Notes -->
            <div class="form-field">
                <label class="form-label">Notes (Optional)</label>
                <textarea wire:model="paymentNotes" class="form-control" placeholder="Add any notes about this payment..."
                    rows="2"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons gap-2">
                <button wire:click="closeModal" class="btn-cancel" wire:loading.attr="disabled">
                    Cancel
                </button>
                <button wire:click="{{ $selectedRepayment ? 'makePayment' : 'payInFull' }}" class="btn-primary"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="{{ $selectedRepayment ? 'makePayment' : 'payInFull' }}">
                        <i class="bi bi-check-circle"></i>
                        Confirm Payment
                    </span>
                    <span wire:loading wire:target="{{ $selectedRepayment ? 'makePayment' : 'payInFull' }}">
                        <x-spinner />
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </x-bottom-sheet>

    <livewire:mobile-app.component.bottom-nav />
</div>
