<div class="screen payments-screen" x-data="{ showFilters: $wire.entangle('showFilters') }">
    <!-- Header -->
    <div class="payments-header">
        {{-- <a href="{{ route('dashboard') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>
        </a> --}}
        <x-link :href="route('dashboard')" class="btn-back" icon="arrow-left" />
        <h1>Transactions</h1>
        <button @click="showFilters = !showFilters" class="filter-btn" :class="{ 'active': showFilters }">
            <i class="bi bi-funnel"></i>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="transaction-stats">
        <div class="row">
            <div class="col-12 mb-2">
                <div class="stat-card">
                    <div class="stat-icon credit">
                        <i class="bi bi-arrow-down-left"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Money In</span>
                        <span class="stat-value credit">${{ number_format($stats['credits'], 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <div class="stat-card">
                    <div class="stat-icon debit">
                        <i class="bi bi-arrow-up-right"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Money Out</span>
                        <span class="stat-value debit">${{ number_format($stats['debits'], 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Net Flow</span>
                        <span class="stat-value {{ $stats['net'] >= 0 ? 'credit' : 'debit' }}">
                            {{ $stats['net'] >= 0 ? '+' : '' }}${{ number_format($stats['net'], 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="search-bar">
        <i class="bi bi-search"></i>
        <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search transactions..."
            class="search-input">
        @if ($searchQuery)
            <button wire:click="$set('searchQuery', '')" class="clear-search">
                <i class="bi bi-x"></i>
            </button>
        @endif
    </div>

    <!-- Clear Filters -->
    @if ($filterType !== 'all' || $filterDate !== 'all' || $sortBy !== 'latest' || $searchQuery)
        <div class="clear-filter">

            <button wire:click="clearFilters" class="clear-filters-btn" wire:loading.attr="disabled">
                <i class="bi bi-x-circle" wire:loading.remove wire:target="clearFilters"></i>
                <x-spinner wire:loading wire:target="clearFilters"></x-spinner>
                Clear All Filters
            </button>
        </div>
    @endif
    <!-- Transactions List -->
    <div class="transactions-list-container pb-4">
        @forelse($transactions as $transaction)
            <div class="transaction-list-item" wire:key="transaction-{{ $transaction->id }}">
                <div class="transaction-avatar" @style([
                    'background: linear-gradient(135deg, #ff416c, #ff4b2b)' => in_array($transaction->transaction_type, ['debit', 'withdrawal']),
                    'background: linear-gradient(135deg, #00C853, #00E676)' => in_array($transaction->transaction_type, ['credit', 'deposit']),
                    'background: linear-gradient(135deg, #667eea, #764ba2)' => $transaction->transaction_type === 'transfer',
                ])>
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
                    <p>
                        {{ ucfirst($transaction->transaction_type) }} • {{ $transaction->created_at->format('g:i A') }}
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
                <div class="empty-state">
                    <i class="bi bi-receipt"></i>
                    <h3>No Transactions Found</h3>
                    <p>
                        @if ($searchQuery || $filterType !== 'all' || $filterDate !== 'all')
                            Try adjusting your filters or search terms
                        @else
                            Your transactions will appear here
                        @endif
                    </p>
                    @if ($searchQuery || $filterType !== 'all' || $filterDate !== 'all')
                        <button wire:click="clearFilters" class="btn-secondary">
                            Clear Filters
                        </button>
                    @endif
                </div>
            @endforelse

            @if ($transactions->count() >= 50)
                <div class="load-more-notice">
                    <i class="bi bi-info-circle"></i>
                    <span>Showing 50 most recent transactions</span>
                </div>
            @endif
        </div>

        <x-bottom-sheet id="showFilters" title="Apply Filter">
            <!-- Type Filter -->
            <div class="filter-group">
                <label class="filter-label">Transaction Type</label>
                <div class="filter-chips">
                    <button wire:click="$set('tempFilterType', 'all')" @class(['filter-chip', 'active' => $tempFilterType === 'all'])>
                        All
                    </button>
                    <button wire:click="$set('tempFilterType', 'credit')" @class(['filter-chip', 'active' => $tempFilterType === 'credit'])>
                        Credit
                    </button>
                    <button wire:click="$set('tempFilterType', 'debit')" @class(['filter-chip', 'active' => $tempFilterType === 'debit'])>
                        Debit
                    </button>
                    <button wire:click="$set('tempFilterType', 'transfer')" @class(['filter-chip', 'active' => $tempFilterType === 'transfer'])>
                        Transfer
                    </button>
                    <button wire:click="$set('tempFilterType', 'withdrawal')" @class(['filter-chip', 'active' => $tempFilterType === 'withdrawal'])>
                        Withdrawal
                    </button>
                    <button wire:click="$set('tempFilterType', 'deposit')" @class(['filter-chip', 'active' => $tempFilterType === 'deposit'])>
                        Deposit
                    </button>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="filter-group">
                <label class="filter-label">Time Period</label>
                <div class="filter-chips">
                    <button wire:click="$set('tempFilterDate', 'all')" @class(['filter-chip', 'active' => $tempFilterDate === 'all'])>
                        All Time
                    </button>
                    <button wire:click="$set('tempFilterDate', 'today')" @class(['filter-chip', 'active' => $tempFilterDate === 'today'])>
                        Today
                    </button>
                    <button wire:click="$set('tempFilterDate', 'week')" @class(['filter-chip', 'active' => $tempFilterDate === 'week'])>
                        This Week
                    </button>
                    <button wire:click="$set('tempFilterDate', 'month')" @class(['filter-chip', 'active' => $tempFilterDate === 'month'])>
                        This Month
                    </button>
                    <button wire:click="$set('tempFilterDate', 'year')" @class(['filter-chip', 'active' => $tempFilterDate === 'year'])>
                        This Year
                    </button>
                </div>
            </div>

            <!-- Sort Filter -->
            <div class="filter-group">
                <label class="filter-label">Sort By</label>
                <div class="filter-chips">
                    <button wire:click="$set('tempSortBy', 'latest')" @class(['filter-chip', 'active' => $tempSortBy === 'latest'])>
                        Latest First
                    </button>
                    <button wire:click="$set('tempSortBy', 'oldest')" @class(['filter-chip', 'active' => $tempSortBy === 'oldest'])>
                        Oldest First
                    </button>
                    <button wire:click="$set('tempSortBy', 'highest')" @class(['filter-chip', 'active' => $tempSortBy === 'highest'])>
                        Highest Amount
                    </button>
                    <button wire:click="$set('tempSortBy', 'lowest')" @class(['filter-chip', 'active' => $tempSortBy === 'lowest'])>
                        Lowest Amount
                    </button>
                </div>
            </div>

            <div class="mt-3 action-buttons gap-2">
                <button @click="$dispatch('close-bottom-sheet', { id: 'showFilters' })" class="btn-cancel"
                    wire:loading.attr="disabled" wire:target="applyFilters">
                    Cancel
                </button>
                <button class="btn-primary" wire:click="applyFilters" wire:loading.attr="disabled"
                    wire:target="applyFilters">
                    <i class="bi bi-check-circle" wire:loading.remove></i>
                    <span wire:loading.remove wire:target="applyFilters">Apply</span>
                    <x-spinner wire:loading wire:target="applyFilters" />
                    <span wire:loading wire:target="applyFilters">Loading</span>
                </button>
            </div>
        </x-bottom-sheet>


        <livewire:mobile-app.component.bottom-nav />

    </div>
