<div class="screen investment-detail-screen">
    <!-- Header -->
    <div class="detail-header">
        <x-link :href="route('stock', ['tab' => 'my-investments'])" class="btn-back" icon="arrow-left" />
        <h1>Investment Details</h1>
        <button wire:click="refreshData" class="refresh-btn">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>

    <!-- Stock Info Card -->
    <div class="detail-stock-card">
        <div class="stock-header-section">
            <div class="stock-logo-large">
                @if ($investment->stock->logo_url)
                    <img src="{{ $investment->stock->logo_url }}" alt="{{ $investment->stock->symbol }}">
                @else
                    <div class="stock-logo-placeholder-large">
                        {{ substr($investment->stock->symbol, 0, 2) }}
                    </div>
                @endif
            </div>
            <div class="stock-title">
                <h2>{{ $investment->stock->symbol }}</h2>
                <p>{{ $investment->stock->name }}</p>
            </div>
            <span @class(['investment-status-badge', 'status-' . $investment->status])>
                {{ ucfirst($investment->status) }}
            </span>
        </div>

        <div class="value-cards-grid">
            <div class="value-card">
                <span class="value-label">Invested</span>
                <span class="value-amount">${{ number_format($investment->amount, 2) }}</span>
                <span class="value-meta">
                    {{ number_format($investment->shares) }} shares @
                    ${{ number_format($investment->purchase_price, 2) }}
                </span>
            </div>
            <div class="value-card">
                <span class="value-label">Current Value</span>
                <span class="value-amount">${{ number_format($investment->current_value, 2) }}</span>
                <span class="value-meta">Current:
                    ${{ number_format($investment->stock->current_price, 2) }}/share</span>
            </div>
            <div class="value-card highlight">
                <span class="value-label">Profit/Loss</span>
                <span @class([
                    'value-amount',
                    'profit' => $investment->profit_loss >= 0,
                    'loss' => $investment->profit_loss < 0,
                ])>
                    {{ $investment->profit_loss >= 0 ? '+' : '' }}${{ number_format($investment->profit_loss, 2) }}
                </span>
                <span class="value-meta">{{ number_format($investment->profit_loss_percentage, 2) }}% return</span>
            </div>
            <div class="value-card">
                <span class="value-label">Total Profit Paid</span>
                <span class="value-amount profit">${{ number_format($investment->total_profit_paid, 2) }}</span>
                <span class="value-meta">{{ $investment->profits()->where('status', 'paid')->count() }} payments</span>
            </div>
        </div>

        @if ($investment->maturity_date)
            <div class="maturity-info">
                <i class="bi bi-calendar-check"></i>
                <div>
                    <strong>Maturity Date</strong>
                    <p>{{ $investment->maturity_date->format('F d, Y') }}
                        ({{ $investment->maturity_date->diffForHumans() }})</p>
                </div>
            </div>
        @endif

        @if ($investment->roi_percentage)
            <div class="roi-info">
                <i class="bi bi-graph-up-arrow"></i>
                <div>
                    <strong>Expected ROI</strong>
                    <p>{{ $investment->roi_percentage }}% over {{ $investment->duration_days }} days</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Tabs -->
    <div class="detail-tabs">
        <button wire:click="switchTab('overview')" @class(['detail-tab', 'active' => $activeTab === 'overview'])>
            Overview
        </button>
        <button wire:click="switchTab('transactions')" @class(['detail-tab', 'active' => $activeTab === 'transactions'])>
            Transactions
        </button>
        <button wire:click="switchTab('profits')" @class(['detail-tab', 'active' => $activeTab === 'profits'])>
            Profits
        </button>
    </div>

    <!-- Tab Content -->
    <div class="detail-content">
        @if ($activeTab === 'overview')
            <div class="overview-section">
                <div class="info-card">
                    <h3><i class="bi bi-info-circle"></i> Investment Information</h3>
                    <div class="info-row">
                        <span>Reference Number</span>
                        <strong>{{ $investment->reference_number }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Investment Type</span>
                        <strong>{{ ucfirst($investment->investment_type ?? 'Standard') }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Account</span>
                        <strong>{{ $investment->userAccount->account_name }}</strong>
                    </div>
                    <div class="info-row">
                        <span>Activated On</span>
                        <strong>{{ $investment->activated_at?->format('M d, Y g:i A') ?? 'Not activated' }}</strong>
                    </div>
                    @if ($investment->completed_at)
                        <div class="info-row">
                            <span>Completed On</span>
                            <strong>{{ $investment->completed_at->format('M d, Y g:i A') }}</strong>
                        </div>
                    @endif
                </div>

                <div class="info-card">
                    <h3><i class="bi bi-graph-up"></i> Performance Metrics</h3>
                    <div class="metric-row">
                        <div class="metric">
                            <span class="metric-label">Opening Price</span>
                            <span
                                class="metric-value">${{ number_format($investment->stock->opening_price, 2) }}</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Day High</span>
                            <span class="metric-value">${{ number_format($investment->stock->day_high, 2) }}</span>
                        </div>
                    </div>
                    <div class="metric-row">
                        <div class="metric">
                            <span class="metric-label">Previous Close</span>
                            <span
                                class="metric-value">${{ number_format($investment->stock->previous_close, 2) }}</span>
                        </div>
                        <div class="metric">
                            <span class="metric-label">Day Low</span>
                            <span class="metric-value">${{ number_format($investment->stock->day_low, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($activeTab === 'transactions')
            <div class="transactions-section">
                @forelse($investment->transactions as $transaction)
                    <div class="transaction-detail-item">
                        <div class="transaction-icon-wrapper">
                            <div class="transaction-icon">
                                @if ($transaction->type === 'profit')
                                    <i class="bi bi-arrow-down-left"></i>
                                @elseif($transaction->type === 'liquidation')
                                    <i class="bi bi-cash-coin"></i>
                                @else
                                    <i class="bi bi-arrow-left-right"></i>
                                @endif
                            </div>
                        </div>
                        <div class="transaction-info">
                            <h4>{{ ucfirst($transaction->type) }}</h4>
                            <p>{{ $transaction->description }}</p>
                            <span
                                class="transaction-date">{{ $transaction->created_at->format('M d, Y g:i A') }}</span>
                        </div>
                        <div class="transaction-amount-detail">
                            <span class="amount-value">${{ number_format($transaction->amount, 2) }}</span>
                            <span class="ref-number">{{ $transaction->reference_number }}</span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state-small">
                        <i class="bi bi-receipt"></i>
                        <p>No transactions yet</p>
                    </div>
                @endforelse
            </div>
        @else
            <div class="profits-section">
                @forelse($investment->profits as $profit)
                    <div class="profit-item">
                        <div class="profit-header">
                            <div class="profit-icon">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div class="profit-info">
                                <h4>{{ ucfirst($profit->type) }} Profit</h4>
                                <p>{{ $profit->description ?? 'Investment profit payment' }}</p>
                            </div>
                            <span @class(['profit-status-badge', 'status-' . $profit->status])>
                                {{ ucfirst($profit->status) }}
                            </span>
                        </div>
                        <div class="profit-details">
                            <div class="profit-amount">
                                <span class="label">Amount</span>
                                <span class="value">${{ number_format($profit->amount, 2) }}</span>
                            </div>
                            <div class="profit-date">
                                <span class="label">{{ $profit->isPaid() ? 'Paid On' : 'Created' }}</span>
                                <span class="value">
                                    {{ ($profit->paid_at ?? $profit->created_at)->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                        <div class="profit-ref">
                            <i class="bi bi-hash"></i>
                            {{ $profit->reference_number }}
                        </div>
                    </div>
                @empty
                    <div class="empty-state-small">
                        <i class="bi bi-piggy-bank"></i>
                        <p>No profits recorded yet</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>

    {{-- <livewire:mobile-app.component.bottom-nav /> --}}
</div>
