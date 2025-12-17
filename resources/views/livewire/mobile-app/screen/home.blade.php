<div class="screen home-screen">
    <!-- Header -->
    <div class="app-header">
        <x-link :href="route('account.profile')" class="icon-btn">
            @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="" class="home-avatar">
            @else
                <span class="initials">
                    {{ Auth::user()->initials() }}
                </span>
            @endif
        </x-link>
        <button @click="toggleDarkMode()" class="icon-btn">
            <i :class="darkMode ? 'bi bi-sun-fill' : 'bi bi-moon-fill'"></i>
        </button>
    </div>

    <!-- Swipeable Balance Cards -->
    <div class="balance-container" x-data="balanceSliderComponent({{ $accounts->count() }})">
        <div class="balance-slider" x-ref="slider" @touchstart="start($event)" @touchmove="move($event)"
            @touchend="end()" :style="`transform: translateX(-${currentSlide * 100}%)`">
            @foreach ($accounts as $account)
                <div class="balance-card">
                    <div class="account-type">
                        {{ Str::ucfirst(Str::replace('_', ' ', $account->account_type)) }} - {{ $account->currency }}
                    </div>

                    <div class="balance">
                        {{ Number::currency($account->balance, $account->currency) }}
                    </div>

                    <button class="account-number">
                        {{ $account->account_number }} <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            @endforeach
        </div>

        {{-- Dots show only if multiple accounts --}}
        @if ($accounts->count() > 1)
            <div class="slide-dots">
                @foreach ($accounts as $index => $account)
                    <span :class="{ 'active': currentSlide === {{ $index }} }"></span>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        @if ($configuration->allow_deposits)
            <x-link :href="route('deposit')" class="action-btn">
                <div class="action-icon">
                    <i class="bi bi-plus-lg"></i>
                </div>
                <span>Add money</span>
            </x-link>
        @endif

        @if ($configuration->allow_transfers)
            <x-link :href="route('transfer')" class="action-btn">
                <div class="action-icon">
                    <i class="bi bi-send"></i>
                </div>
                <span>Transfer</span>
            </x-link>
        @endif


        @if ($enable_stocks)
            <x-link :href="route('stock')" class="action-btn">
                <div class="action-icon">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
                <span>Stock</span>
            </x-link>
        @endif

        <x-link :href="route('more')" class="action-btn">
            <div class="action-icon">
                <i class="bi bi-three-dots"></i>
            </div>
            <span>More</span>
        </x-link>
    </div>

    <!-- Swipeable Notification Banners -->
    <div class="notification-container" x-data="notificationSliderComponent({{ count($notifications) }})">
        <div class="notification-slider" x-ref="notificationSlider" @touchstart="start($event)"
            @touchmove="move($event)" @touchend="end()" :style="`transform: translateX(-${current * 100}%)`">
            @foreach ($notifications as $notification)
                <div class="notification-banner">
                    <div class="notification-content">
                        <div class="notification-icon">{{ $notification['icon'] }}</div>
                        <div class="notification-text">
                            <h4>{{ $notification['title'] }}</h4>
                            <p>{{ $notification['message'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if (count($notifications) > 1)
            <div class="slide-dots mt-3">
                @foreach ($notifications as $index => $notification)
                    <span :class="{ 'active': current === {{ $index }} }"></span>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Recent Transactions Section -->
    <div class="task-section">
        <h3>Recent Transactions</h3>
        @forelse ($recentTransactions as $transaction)
            <div class="transaction-item">
                <div class="transaction-avatar" @style([
                    'background: linear-gradient(135deg, #ff416c, #ff4b2b)' => $transaction->transaction_type === 'debit' || $transaction->transaction_type === 'withdrawal',
                    'background: linear-gradient(135deg, #00C853, #00E676)' => $transaction->transaction_type === 'credit' || $transaction->transaction_type === 'deposit',
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
                        {{ ucfirst($transaction->transaction_type) }} •
                        {{ $transaction->created_at->format('g:i A') }}
                        @if ($transaction->status != 'completed')
                            <i class="bi bi-x-circle-fill text-danger"></i>
                        @endif
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
                    <x-link :href="route('payments')" class="py-1 px-4 rounded shadow view-all-btn">
                        View all
                    </x-link>
                </div>
            @endif
        </div>

        <livewire:mobile-app.component.bottom-nav />
    </div>
