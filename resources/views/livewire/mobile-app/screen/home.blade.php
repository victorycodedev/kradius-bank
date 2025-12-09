<div class="screen home-screen">
    <!-- Header -->
    <div class="app-header">
        <button class="icon-btn">
            <span class="initials">
                {{ Auth::user()->initials() }}
            </span>
        </button>
        <button @click="toggleDarkMode()" class="icon-btn">
            <i :class="darkMode ? 'bi bi-sun-fill' : 'bi bi-moon-fill'"></i>
        </button>
    </div>

    <!-- Swipeable Balance Cards -->
    <div class="balance-container" x-data="balanceSliderComponent({{ $accounts->count() }})">
        <div class="balance-slider" x-ref="slider" @touchstart="start($event)" @touchmove="move($event)" @touchend="end()"
            :style="`transform: translateX(-${currentSlide * 100}%)`">
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
        <a class="action-btn" href="{{ route('deposit') }}" wire:navigate>
            <div class="action-icon">
                <i class="bi bi-plus-lg"></i>
            </div>
            <span>Add money</span>
        </a>
        <a class="action-btn" href="{{ route('transfer') }}" wire:navigate>
            <div class="action-icon">
                <i class="bi bi-send"></i>
            </div>
            <span>Transfer</span>
        </a>
        <a class="action-btn" href="{{ route('stock') }}" wire:navigate>
            <div class="action-icon">
                <i class="bi bi-pie-chart-fill"></i>
            </div>
            <span>Stock</span>
        </a>
        <a class="action-btn" href="{{ route('more') }}" wire:navigate>
            <div class="action-icon">
                <i class="bi bi-three-dots"></i>
            </div>
            <span>More</span>
        </a>
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

    <!-- Task Section -->
    <div class="task-section">
        <h3>Recent Transactions</h3>
        @forelse($recentTransactions as $transaction)
            <div class="transaction-item">
                <div class="transaction-avatar"></div>
                <div class="transaction-details">
                    <h4>{{ Str::limit($transaction->description ?? 'Transaction', 30) }}</h4>
                    <p>
                        {{ ucfirst($transaction->transaction_type) }} •
                        {{ $transaction->created_at->format('M j, g:i A') }}
                    </p>
                </div>
                <div
                    class="transaction-amount {{ $transaction->transaction_type === 'credit' ? 'positive' : 'negative' }}">
                    {{ $transaction->transaction_type === 'credit' ? '+' : '-' }}
                    {{ Number::currency($transaction->amount, $transaction->currency) }}
                </div>
            </div>
        @empty
            <div class="text-center py-4">
                <p class="text-muted">No transactions yet</p>
            </div>
        @endforelse

        @if ($recentTransactions->count() > 0)
            <button class="view-all-btn">View all</button>
        @endif
    </div>

    <livewire:mobile-app.bottom-nav />
</div>
