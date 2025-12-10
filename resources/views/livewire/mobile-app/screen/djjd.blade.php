<!-- Content -->
<div class="investment-content">
    <!-- Stocks List -->
    <div x-show="activeTab === 'stocks'" x-transition>
        <div class="stocks-list">
            @forelse($stocks as $stock)
                <div class="stock-card" @click="selectStock({{ $stock->id }}, {{ json_encode($stock) }})">
                    <div class="stock-header">
                        <div class="stock-logo">
                            @if ($stock->logo_url)
                                <img src="{{ $stock->logo_url }}" alt="{{ $stock->symbol }}">
                            @else
                                <div class="stock-logo-placeholder">
                                    {{ substr($stock->symbol, 0, 2) }}
                                </div>
                            @endif
                        </div>
                        <div class="stock-info">
                            <h3>{{ $stock->symbol }}</h3>
                            <p>{{ $stock->name }}</p>
                        </div>
                        @if ($stock->is_featured)
                            <span class="featured-badge">
                                <i class="bi bi-star-fill"></i>
                            </span>
                        @endif
                    </div>

                    <div class="stock-details">
                        <div class="stock-price">
                            <span class="price">${{ number_format($stock->current_price, 2) }}</span>
                            <span
                                :class="{
                                    'price-change': true,
                                    'positive': {{ $stock->price_change >= 0 ? 'true' : 'false' }},
                                    'negative': {{ $stock->price_change < 0 ? 'true' : 'false' }}
                                }">
                                <i class="bi bi-{{ $stock->price_change >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ number_format(abs($stock->price_change_percentage), 2) }}%
                            </span>
                        </div>
                        <div class="stock-meta">
                            <span><i class="bi bi-people"></i> {{ $stock->investment_count }} investors</span>
                            <span><i class="bi bi-cash"></i> Min:
                                ${{ number_format($stock->minimum_investment) }}</span>
                        </div>
                    </div>

                    <div class="stock-action">
                        <span>View Details</span>
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="bi bi-graph-down"></i>
                    <h3>No Stocks Available</h3>
                    <p>Check back later for investment opportunities</p>
                </div>
            @endforelse
        </div>
    </div>

    <div x-show="activeTab === 'my-investments'" x-transition>
        <div class="investments-list">
            @forelse($myInvestments as $investment)
                <a href="{{ route('mobile.investment-detail', $investment->id) }}" class="investment-card">
                    <div class="investment-header-row">
                        <div class="investment-stock">
                            <div class="stock-logo-small">
                                @if ($investment->stock->logo_url)
                                    <img src="{{ $investment->stock->logo_url }}"
                                        alt="{{ $investment->stock->symbol }}">
                                @else
                                    <div class="stock-logo-placeholder-small">
                                        {{ substr($investment->stock->symbol, 0, 2) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h4>{{ $investment->stock->symbol }}</h4>
                                <p>{{ $investment->shares }} shares</p>
                            </div>
                        </div>
                        <span :class="'investment-status status-{{ $investment->status }}'">
                            {{ ucfirst($investment->status) }}
                        </span>
                    </div>

                    <div class="investment-values">
                        <div class="value-item">
                            <span class="label">Invested</span>
                            <span class="value">${{ number_format($investment->amount, 2) }}</span>
                        </div>
                        <div class="value-item">
                            <span class="label">Current Value</span>
                            <span class="value">${{ number_format($investment->current_value, 2) }}</span>
                        </div>
                        <div class="value-item">
                            <span class="label">Profit/Loss</span>
                            <span
                                :class="{
                                    'value': true,
                                    'profit': {{ $investment->profit_loss >= 0 ? 'true' : 'false' }},
                                    'loss': {{ $investment->profit_loss < 0 ? 'true' : 'false' }}
                                }">
                                {{ $investment->profit_loss >= 0 ? '+' : '' }}${{ number_format($investment->profit_loss, 2) }}
                                <small>({{ number_format($investment->profit_loss_percentage, 2) }}%)</small>
                            </span>
                        </div>
                    </div>

                    @if ($investment->maturity_date)
                        <div class="investment-footer">
                            <span>
                                <i class="bi bi-calendar"></i>
                                Matures: {{ $investment->maturity_date->format('M d, Y') }}
                            </span>
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    @endif
                </a>
            @empty
                <div class="empty-state">
                    <i class="bi bi-wallet2"></i>
                    <h3>No Investments Yet</h3>
                    <p>Start investing in stocks to build your portfolio</p>
                    <button @click="switchTab('stocks')" class="btn-primary">
                        Browse Stocks
                    </button>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Investment Modal -->
<div x-show="showInvestModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="modal-overlay"
    @click="closeModal()" style="display: none;">
    <div class="modal-content" @click.stop x-show="showInvestModal"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95">
        <div class="modal-header">
            <h2 x-text="'Invest in ' + (selectedStock?.symbol || '')"></h2>
            <button @click="closeModal()" class="close-modal">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="stock-modal-info" x-show="selectedStock">
                <div class="stock-logo-large">
                    <template x-if="selectedStock?.logo_url">
                        <img :src="selectedStock.logo_url" :alt="selectedStock.symbol">
                    </template>
                    <template x-if="!selectedStock?.logo_url">
                        <div class="stock-logo-placeholder-large" x-text="selectedStock?.symbol.substring(0, 2)">
                        </div>
                    </template>
                </div>
                <div>
                    <h3 x-text="selectedStock?.name"></h3>
                    <p class="current-price">$<span x-text="selectedStock?.current_price.toFixed(2)"></span> per
                        share</p>
                </div>
            </div>

            <p class="stock-description" x-show="selectedStock?.description" x-text="selectedStock?.description">
            </p>

            <div class="investment-form">
                <div class="form-group">
                    <label>Investment Amount</label>
                    <div class="input-with-icon">
                        <span class="input-icon">$</span>
                        <input type="number" x-model.number="investmentAmount" class="form-control" placeholder="0.00"
                            :min="selectedStock?.minimum_investment" step="0.01" @input="calculateShares()">
                    </div>
                    <small class="form-hint">
                        Min: $<span x-text="selectedStock?.minimum_investment"></span>
                        <template x-if="selectedStock?.maximum_investment">
                            • Max: $<span x-text="selectedStock?.maximum_investment"></span>
                        </template>
                    </small>
                    <span class="error-message" x-show="errors.investmentAmount"
                        x-text="errors.investmentAmount"></span>
                </div>

                <div class="form-group">
                    <label>Select Account</label>
                    <select x-model="selectedAccount" class="form-control">
                        <option value="">Choose account...</option>
                        {{-- @foreach (auth()->user()->userAccounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->account_name }} ({{ $account->currency }}
                                    {{ number_format($account->balance, 2) }})
                                </option>
                            @endforeach --}}
                    </select>
                    <span class="error-message" x-show="errors.selectedAccount"
                        x-text="errors.selectedAccount"></span>
                </div>

                <div class="investment-summary" x-show="calculatedShares > 0">
                    <div class="summary-row">
                        <span>Shares to purchase</span>
                        <strong x-text="calculatedShares.toFixed(4)"></strong>
                    </div>
                    <div class="summary-row">
                        <span>Price per share</span>
                        <strong>$<span x-text="selectedStock?.current_price.toFixed(2)"></span></strong>
                    </div>
                    <div class="summary-row total">
                        <span>Total Amount</span>
                        <strong>$<span x-text="investmentAmount.toFixed(2)"></span></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button @click="closeModal()" class="btn-secondary">Cancel</button>
            <button @click="invest()" class="btn-primary" :disabled="isInvesting">
                <i class="bi bi-check-circle"></i>
                <span x-text="isInvesting ? 'Processing...' : 'Invest Now'"></span>
            </button>
        </div>
    </div>
</div>





<!-- Recent Transactions Section -->
<div class="task-section">
    <h3>Recent Transactions</h3>
    @forelse($recentTransactions as $transaction)
        <div @class(['transaction-item'])>
            <div class="transaction-avatar"
                style="
            @if ($transaction->transaction_type === 'credit' || $transaction->transaction_type === 'deposit') background: linear-gradient(135deg, #00C853, #00E676);
            @elseif($transaction->transaction_type === 'debit' || $transaction->transaction_type === 'withdrawal')
                background: linear-gradient(135deg, #ff416c, #ff4b2b);
            @elseif($transaction->transaction_type === 'transfer')
                background: linear-gradient(135deg, #667eea, #764ba2); @endif
        ">
            </div>
            <div class="transaction-icon">
                @switch($transaction->transaction_type)
                    @case('credit')
                        <i class="bi bi-arrow-down-left"></i>
                    @break

                    @case('debit')
                        <i class="bi bi-arrow-up-right"></i>
                    @break

                    @case('transfer')
                        <i class="bi bi-arrow-left-right"></i>
                    @break

                    @case('withdrawal')
                        <i class="bi bi-cash-stack"></i>
                    @break

                    @case('deposit')
                        <i class="bi bi-wallet2"></i>
                    @break

                    @default
                        <i class="bi bi-currency-dollar"></i>
                @endswitch
            </div>
        </div>

        <div class="transaction-details">
            <h4>{{ $transaction->description ?? 'Transaction' }}</h4>
            <p>{{ ucfirst($transaction->transaction_type) }} • {{ $transaction->created_at->format('g:i A') }}
            </p>
        </div>

        <div @class([
            'transaction-amount',
            'positive' => in_array($transaction->transaction_type, [
                'credit',
                'deposit',
            ]),
            'negative' => in_array($transaction->transaction_type, [
                'debit',
                'withdrawal',
            ]),
        ])>
            @if (in_array($transaction->transaction_type, ['credit', 'deposit']))
                + {{ $transaction->currency_symbol ?? '£' }} {{ number_format($transaction->amount, 2) }}
            @else
                - {{ $transaction->currency_symbol ?? '£' }} {{ number_format($transaction->amount, 2) }}
            @endif
        </div>
</div>
@empty
    <div class="text-center py-4">
        <p class="text-muted">No recent transactions</p>
    </div>
    @endforelse

    @if ($recentTransactions->count() > 0)
        <div class="text-center mt-3 text-dark pb-3">
            <a class="py-1 px-4 rounded shadow view-all-btn" href="{{ route('payments') }}" wire:navigate>
                View all
            </a>
        </div>
    @endif
    </div>



    <div class="transaction-avatar" @style([
        'background: linear-gradient(135deg, #ff416c, #ff4b2b)' => in_array($transaction->transaction_type, ['debit', 'withdrawal']),
        'background: linear-gradient(135deg, #00C853, #00E676)' => in_array($transaction->transaction_type, ['credit', 'deposit']),
        'background: linear-gradient(135deg, #667eea, #764ba2)' => $transaction->transaction_type === 'transfer',
    ])>

    </div>

    <div class="transaction-details">
        <h4>{{ $transaction->description ?? 'Transaction' }}</h4>
        <p>
            <span class="transaction-type-badge type-{{ $transaction->transaction_type }}">
                {{ ucfirst($transaction->transaction_type) }}
            </span>
            <span class="transaction-date">{{ $transaction->created_at->format('M d, Y • g:i A') }}</span>
        </p>
        @if ($transaction->reference)
            <p class="transaction-ref">
                <i class="bi bi-hash"></i> {{ $transaction->reference }}
            </p>
        @endif
    </div>

    <div class="transaction-amount-section">
        <div @class([
            'transaction-amount',
            'positive' => in_array($transaction->transaction_type, [
                'credit',
                'deposit',
            ]),
            'negative' => in_array($transaction->transaction_type, [
                'debit',
                'withdrawal',
            ]),
        ])>
            @if (in_array($transaction->transaction_type, ['credit', 'deposit']))
                +{{ $transaction->currency_symbol ?? '$' }}{{ number_format($transaction->amount, 2) }}
            @else
                -{{ $transaction->currency_symbol ?? '$' }}{{ number_format($transaction->amount, 2) }}
            @endif
        </div>
        <span class="transaction-status status-{{ $transaction->status }}">
            {{ ucfirst($transaction->status) }}
        </span>
    </div>
    </div>
