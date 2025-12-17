<div class="screen investment-screen" x-data="{
    activeTab: $wire.entangle('activeTab'),
    selectedStock: null,
    showInvestModal: $wire.entangle('showInvestModal'),
    investmentAmount: 0,
    selectedAccount: '',
    calculatedShares: 0,
    isInvesting: false,
    errors: {},

    switchTab(tab) {
        this.activeTab = tab;
    },

    selectStock(stockId, stockData) {
        this.selectedStock = stockData;
        this.showInvestModal = true;
        this.resetForm();
        $wire.selectedStockId = stockId;
        document.body.style.overflow = 'hidden';
    },

    closeModal() {
        {{-- this.showInvestModal = false; --}}
        this.$dispatch('close-bottom-sheet', { id: 'showInvestModal' });
        this.selectedStock = null;
        this.resetForm();
        document.body.style.overflow = '';
    },

    resetForm() {
        this.investmentAmount = 0;
        this.selectedAccount = '';
        this.calculatedShares = 0;
        this.errors = {};
    },

    calculateShares() {
        if (this.investmentAmount > 0 && this.selectedStock?.current_price) {
            this.calculatedShares = this.investmentAmount / parseFloat(this.selectedStock.current_price);
        } else {
            this.calculatedShares = 0;
        }
        $wire.amount = this.investmentAmount;
        $wire.accountId = this.selectedAccount;
    },
}">
    <!-- Header -->
    <div class="investment-header">
        <x-link :href="route('dashboard')" class="btn-back" icon="arrow-left" />
        <h1>Stock Investment</h1>
    </div>

    <!-- Tabs -->
    <div class="investment-tabs">
        <button @click="switchTab('stocks')" class="tab-btn" :class="{ 'active': activeTab === 'stocks' }">
            <i class="bi bi-graph-up"></i>
            Stocks
        </button>
        <button @click="switchTab('my-investments')" class="tab-btn"
            :class="{ 'active': activeTab === 'my-investments' }">
            <i class="bi bi-wallet2"></i>
            My Stocks
        </button>
    </div>

    <!-- Content -->
    <div class="investment-content">
        <!-- Stocks List -->
        <div x-show="activeTab === 'stocks'" style="display: none;" class="pb-4">
            <div class="stocks-list">
                @forelse($stocks as $stock)
                    <div class="stock-card" @click="selectStock({{ $stock->id }}, {{ Js::from($stock) }})">
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
                                <span class="price-change {{ $stock->price_change >= 0 ? 'positive' : 'negative' }}">
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

        <!-- My Investments List -->
        <div x-show="activeTab === 'my-investments'" class="pb-4">
            <div class="investments-list">
                @forelse($myInvestments as $investment)
                    <x-link :href="route('investment-detail', $investment->id)" class="investment-card">
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
                            <span class="investment-status status-{{ $investment->status }}">
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
                                <span class="value {{ $investment->profit_loss >= 0 ? 'profit' : 'loss' }}">
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
                    </x-link>
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

    <x-bottom-sheet id="showInvestModal">

        <div class="stock-modal-info" x-show="selectedStock">
            <div class="stock-logo-large">
                <template x-if="selectedStock?.logo_url">
                    <img :src="selectedStock.logo_url" :alt="selectedStock.symbol">
                </template>
                <template x-if="!selectedStock?.logo_url">
                    <div class="stock-logo-placeholder-large" x-text="selectedStock?.symbol?.substring(0, 2)">
                    </div>
                </template>
            </div>
            <div>
                <h3 x-text="selectedStock?.name"></h3>
                <p class="current-price">
                    $<span x-text="parseFloat(selectedStock?.current_price || 0).toFixed(2)"></span> per share
                </p>
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
                        :min="selectedStock?.minimum_investment" step="0.01" @input="calculateShares()"
                        wire:loading.attr="disabled" wire:target="invest">
                </div>
                <small class="form-hint">
                    Min: $<span x-text="selectedStock?.minimum_investment"></span>
                    <template x-if="selectedStock?.maximum_investment">
                        <span> • Max: $<span x-text="selectedStock?.maximum_investment"></span></span>
                    </template>
                </small>
                @error('amount')
                    <div class="text-danger small">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label>Select Account</label>
                <select wire:model='accountId' class="form-control" wire:loading.attr="disabled"
                    wire:target="invest">
                    <option value="">Choose account...</option>
                    @foreach (Auth::user()->accounts as $account)
                        <option value="{{ $account->id }}">
                            {{ $account->account_number }} ({{ $account->currency }}
                            {{ number_format($account->balance, 2) }})
                        </option>
                    @endforeach
                </select>
                @error('accountId')
                    <div class="text-danger small">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="investment-summary"
                x-show="investmentAmount > 0 && selectedStock?.minimum_investment && investmentAmount >= parseFloat(selectedStock.minimum_investment)">
                <div class="summary-row">
                    <span>Shares to purchase</span>
                    <strong x-text="calculatedShares.toFixed(4)"></strong>
                </div>
                <div class="summary-row">
                    <span>Price per share</span>
                    <strong>$<span x-text="parseFloat(selectedStock?.current_price || 0).toFixed(2)"></span></strong>
                </div>
                <div class="summary-row total">
                    <span>Total Amount</span>
                    <strong>$<span x-text="(investmentAmount || 0).toFixed(2)"></span></strong>
                </div>
            </div>
        </div>

        @if ($terms)
            <div class="terms-banner mt-3">
                <i class="bi bi-info-circle"></i>
                <div>
                    <h4>Accept Terms</h4>
                    <p>{{ $terms }}</p>
                </div>
            </div>
        @endif

        <div class="mt-3 action-buttons gap-2">
            <button @click="closeModal()" class="btn-cancel" wire:loading.attr="disabled" wire:target="invest">
                Cancel
            </button>
            <button class="btn-primary" wire:click="invest" wire:loading.attr="disabled" wire:target="invest">
                <i class="bi bi-check-circle" wire:loading.remove></i>
                <span wire:loading.remove wire:target="invest">Invest Now</span>
                <x-spinner wire:loading wire:target="invest" />
                <span wire:loading wire:target="invest">Loading</span>
            </button>
        </div>

    </x-bottom-sheet>

    <livewire:mobile-app.component.bottom-nav />
</div>
