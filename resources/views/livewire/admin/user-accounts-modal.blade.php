<div>
    {{-- Header with Create Button --}}
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
            Bank Accounts ({{ $accounts->count() }})
        </h3>
        {{ $this->createAccountAction }}
    </div>

    {{-- Accounts List --}}
    <div class="space-y-4">
        @forelse($accounts as $account)
            <x-filament::section>
                <div class="space-y-3">
                    {{-- Header --}}
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                                    {{ $account->account_number }}
                                </h3>
                                @if ($account->is_primary)
                                    <x-filament::badge color="warning" size="xs" icon="heroicon-o-star">
                                        Primary
                                    </x-filament::badge>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ ucfirst($account->account_type) }} • {{ ucfirst($account->account_tier) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-filament::badge :color="$account->status === 'active' ? 'success' : 'danger'">
                                {{ ucfirst($account->status) }}
                            </x-filament::badge>
                        </div>
                    </div>

                    {{-- Balance Info --}}
                    <div class="grid gap-4" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                        <div class="space-y-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Balance</p>
                            <p class="text-lg font-bold text-gray-950 dark:text-white">
                                {{ $account->currency }} {{ number_format($account->balance, 2) }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Interest Rate</p>
                            <p class="text-lg font-bold text-gray-950 dark:text-white">
                                {{ $account->interest_rate }}%
                            </p>
                        </div>
                    </div>

                    {{-- Limits --}}
                    @if ($account->limits)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Account Limits</p>
                                {{ ($this->editLimitsAction)(['accountId' => $account->id]) }}
                            </div>
                            <div class="grid gap-2" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
                                <div class="space-y-1">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Daily Transfer</p>
                                    <p class="text-sm font-semibold text-gray-950 dark:text-white">
                                        ₦{{ number_format($account->limits->daily_transfer_limit, 0) }}
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Daily Withdrawal</p>
                                    <p class="text-sm font-semibold text-gray-950 dark:text-white">
                                        ₦{{ number_format($account->limits->daily_withdrawal_limit, 0) }}
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Single Transaction</p>
                                    <p class="text-sm font-semibold text-gray-950 dark:text-white">
                                        ₦{{ number_format($account->limits->single_transaction_limit, 0) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Account Info --}}
                    @if ($account->frozen)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
                            <x-filament::badge color="danger" size="sm" icon="heroicon-o-lock-closed">
                                Frozen
                            </x-filament::badge>
                            @if ($account->frozen_reason)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Reason: {{ $account->frozen_reason }}
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                {{ ($this->changeStatusAction)(['accountId' => $account->id]) }}
                                @if (!$account->is_primary)
                                    {{ ($this->setPrimaryAction)(['accountId' => $account->id]) }}
                                @endif
                            </div>
                            <div>
                                {{ ($this->deleteAccountAction)(['accountId' => $account->id]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @empty
            <div class="py-8 text-center">
                <div class="mb-4">
                    <x-filament::icon icon="heroicon-o-banknotes" class="mx-auto h-12 w-12 text-gray-400" />
                </div>
                <p class="text-gray-500 dark:text-gray-400 mb-4">No accounts found for this user.</p>
                {{ $this->createAccountAction }}
            </div>
        @endforelse
    </div>

    <x-filament-actions::modals />
</div>
