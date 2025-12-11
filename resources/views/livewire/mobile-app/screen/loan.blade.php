<div class="screen loan-screen" x-data="{
    activeTab: $wire.entangle('activeTab'),
    showApplyModal: $wire.entangle('showApplyModal'),
    amount: $wire.entangle('amount'),
    durationMonths: $wire.entangle('durationMonths'),
}">
    <!-- Header -->
    <div class="loan-header">
        <x-link :href="route('dashboard')" class="btn-back" icon="arrow-left" />
        <h1>Loans</h1>
    </div>

    <!-- Tabs -->
    <div class="loan-tabs">
        <button @click="activeTab = 'available'" :class="{ 'tab-btn': true, 'active': activeTab === 'available' }">
            <i class="bi bi-cash-stack"></i>
            Available Loans
        </button>
        <button @click="activeTab = 'my-loans'" :class="{ 'tab-btn': true, 'active': activeTab === 'my-loans' }">
            <i class="bi bi-wallet2"></i>
            My Loans
        </button>
    </div>

    <!-- Content -->
    <div class="loan-content">
        <!-- Available Loans Tab -->
        <div x-show="activeTab === 'available'" class="pb-4" x-transition>
            <div class="loan-types-list">
                @forelse($loanTypes as $loanType)
                    <div class="loan-type-card"
                        @click="showApplyModal = true; $wire.selectLoanType({{ $loanType->id }})">
                        <div class="loan-type-header">
                            <div class="loan-type-icon">
                                <i class="bi bi-{{ $loanType->icon ?? 'cash-coin' }}"></i>
                            </div>
                            <div class="loan-type-info">
                                <h3>{{ $loanType->name }}</h3>
                                <p>{{ $loanType->description }}</p>
                            </div>
                            @if ($loanType->is_featured)
                                <span class="featured-badge">
                                    <i class="bi bi-star-fill"></i>
                                </span>
                            @endif
                        </div>

                        <div class="loan-type-details">
                            <div class="detail-item">
                                <span class="label">Interest Rate</span>
                                <span class="value">{{ $loanType->interest_rate }}% p.a.</span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Amount Range</span>
                                <span class="value">
                                    ${{ number_format($loanType->min_amount) }} -
                                    ${{ number_format($loanType->max_amount) }}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Duration</span>
                                <span class="value">
                                    {{ $loanType->min_duration }}-{{ $loanType->max_duration }} months
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Processing Time</span>
                                <span class="value">{{ $loanType->processing_time ?? '2-3 days' }}</span>
                            </div>
                        </div>

                        <div class="loan-type-action">
                            <span>Apply Now</span>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-cash-stack"></i>
                        <h3>No Loan Types Available</h3>
                        <p>Check back later for loan opportunities</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- My Loans Tab -->
        <div x-show="activeTab === 'my-loans'" class="pb-4" x-transition>
            <div class="my-loans-list">
                @forelse($myLoans as $loan)
                    <a href="{{ route('loanDetails', $loan->id) }}" class="my-loan-card text-decoration-none">
                        <div class="loan-card-header">
                            <div class="loan-type-badge">
                                {{ $loan->loanType->name }}
                            </div>
                            <span class="loan-status status-{{ $loan->status }}">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </div>

                        <div class="loan-amount-section">
                            <div class="loan-amount">
                                <span class="label">Loan Amount</span>
                                <h3>${{ number_format($loan->amount, 2) }}</h3>
                            </div>
                            @if ($loan->isActive())
                                <div class="outstanding-balance">
                                    <span class="label">Outstanding</span>
                                    <h4>${{ number_format($loan->outstanding_balance, 2) }}</h4>
                                </div>
                            @endif
                        </div>

                        <div class="loan-details-grid">
                            <div class="detail-box">
                                <i class="bi bi-percent"></i>
                                <div>
                                    <span class="detail-label">Interest Rate</span>
                                    <span class="detail-value">{{ $loan->interest_rate }}%</span>
                                </div>
                            </div>
                            <div class="detail-box">
                                <i class="bi bi-calendar"></i>
                                <div>
                                    <span class="detail-label">Duration</span>
                                    <span class="detail-value">{{ $loan->duration_months }} months</span>
                                </div>
                            </div>
                            <div class="detail-box">
                                <i class="bi bi-calendar-check"></i>
                                <div>
                                    <span class="detail-label">Monthly Payment</span>
                                    <span class="detail-value">${{ number_format($loan->monthly_payment, 2) }}</span>
                                </div>
                            </div>
                            <div class="detail-box">
                                <i class="bi bi-cash"></i>
                                <div>
                                    <span class="detail-label">Total Payable</span>
                                    <span class="detail-value">${{ number_format($loan->total_payable, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="loan-meta">
                            <span>
                                <i class="bi bi-hash"></i>
                                {{ $loan->reference_number }}
                            </span>
                            <span>
                                <i class="bi bi-clock"></i>
                                {{ $loan->created_at->format('M j, Y') }}
                            </span>
                        </div>

                        @if ($loan->due_date)
                            <div class="loan-due-date">
                                <i class="bi bi-exclamation-circle"></i>
                                Next payment due: {{ $loan->due_date->format('M j, Y') }}
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="empty-state">
                        <i class="bi bi-wallet2"></i>
                        <h3>No Loans Yet</h3>
                        <p>You haven't applied for any loans</p>
                        <button @click="activeTab = 'available'" class="btn-primary">
                            Browse Loans
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Apply for Loan Bottom Sheet -->
    <x-bottom-sheet id="showApplyModal" title="Apply for Loan">
        <div class="loan-application-form">
            <!-- Loan Type Info -->
            <div class="selected-loan-info">
                <h3>{{ $selectedLoanType?->name }}</h3>
                <p>{{ $selectedLoanType?->description }}</p>
            </div>

            <!-- Amount -->
            <div>
                <label class="form-label">Loan Amount</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" wire:model.live="amount" class="form-control" placeholder="0.00"
                        step="100">
                </div>
                <small class="form-hint">
                    Min: <span>${{ $selectedLoanType?->min_amount }}</span>
                    • Max: <span>${{ $selectedLoanType?->max_amount }}</span>
                </small>
                @error('amount')
                    <div class="text-danger small">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Duration -->
            <div>
                <label class="form-label">Loan Duration (Months)</label>
                <select class="form-select" wire:model.live="durationMonths">
                    <option value="">Select Month</option>
                    @foreach ($monthsList as $month)
                        <option>{{ $month }}</option>
                    @endforeach
                </select>
                <small class="form-hint">
                    Min: <span>{{ $selectedLoanType?->min_duration_months }}</span>
                    • Max: <span>{{ $selectedLoanType?->max_duration_months }}</span>
                </small>
                @error('durationMonths')
                    <div class="text-danger small">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Loan Calculator Summary -->
            @if ($calculatedMonthlyPayment > 0)
                <div class="loan-calculator-summary">
                    <h4>Loan Summary</h4>
                    <div class="summary-item">
                        <span>Monthly Payment</span>
                        <strong>${{ number_format($calculatedMonthlyPayment, 2) }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>Total Interest</span>
                        <strong>${{ number_format($calculatedInterest, 2) }}</strong>
                    </div>
                    <div class="summary-item summary-total">
                        <span>Total Payable</span>
                        <strong>${{ number_format($calculatedTotalPayable, 2) }}</strong>
                    </div>
                </div>
            @endif

            <!-- Purpose -->
            <div class="form-field">
                <label class="form-label">Purpose of Loan</label>
                <textarea wire:model="purpose" class="form-control" placeholder="Describe why you need this loan..." rows="3"></textarea>
                @error('purpose')
                    <div class="text-danger small">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Employment Status -->
            <div class="form-field">
                <label class="form-label">Employment Status</label>
                <select wire:model="employmentStatus" class="form-select">
                    <option value="">Select employment status...</option>
                    <option value="employed">Employed</option>
                    <option value="self_employed">Self Employed</option>
                    <option value="business_owner">Business Owner</option>
                    <option value="freelancer">Freelancer</option>
                    <option value="unemployed">Unemployed</option>
                    <option value="student">Student</option>
                </select>
                @error('employmentStatus')
                    <div class="text-danger small">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Monthly Income -->
            <div class="form-field">
                <label class="form-label">Monthly Income</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" wire:model="monthlyIncome" class="form-control" placeholder="0.00"
                        step="100">
                </div>
                @error('monthlyIncome')
                    <div class="text-danger small">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Additional Info -->
            <div class="form-field">
                <label class="form-label">Additional Information (Optional)</label>
                <textarea wire:model="additionalInfo" class="form-control" placeholder="Any additional information..."
                    rows="2"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons gap-2">
                <button @click="$wire.closeModal()" class="btn-cancel">
                    Cancel
                </button>
                <button wire:click="applyForLoan" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="applyForLoan">
                        <i class="bi bi-check-circle"></i>
                        Submit Application
                    </span>
                    <span wire:loading wire:target="applyForLoan">
                        <x-spinner />
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </x-bottom-sheet>

    <livewire:mobile-app.component.bottom-nav />
</div>
