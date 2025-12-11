<div class="screen cards-screen">
    <!-- Header -->
    <div class="investment-header">
        <x-link :href="route('more')" class="btn-back" icon="arrow-left" />
        <h1>My Cards</h1>
        <button wire:click="openAddCardModal" class="add-card-btn">
            <i class="bi bi-plus-lg"></i>
        </button>
    </div>

    <!-- Cards List -->
    <div class="cards-container">
        @forelse($cards as $card)
            <div class="card-item" wire:key="card-{{ $card->id }}">
                <!-- Card Visual -->
                <div class="credit-card {{ $card->card_brand }} {{ $card->isActive() ? '' : 'blocked' }}">
                    <div class="card-header-row">
                        <span class="card-brand-name">{{ ucfirst($card->card_brand) }}</span>
                        <span class="card-type-badge">{{ ucfirst($card->card_type) }}</span>
                    </div>

                    <div class="card-chip">
                        <i class="bi bi-sim"></i>
                    </div>

                    <div class="card-number">{{ $this->maskCardNumber($card->card_number) }}</div>

                    <div class="card-footer-row">
                        <div class="card-holder">
                            <span class="card-label">Card Holder</span>
                            <span class="card-value">{{ $card->card_holder_name }}</span>
                        </div>
                        <div class="card-expiry">
                            <span class="card-label">Expires</span>
                            <span class="card-value">{{ $card->expiry_date->format('m/y') }}</span>
                        </div>
                    </div>

                    @if (!$card->isActive())
                        <div class="card-blocked-overlay">
                            <i class="bi bi-shield-slash"></i>
                            <span>BLOCKED</span>
                        </div>
                    @endif
                </div>

                <!-- Card Details -->
                <div class="card-details-section">
                    <div class="detail-row">
                        <span class="detail-label">Linked Account: </span>
                        <span class="detail-value">{{ $card->userAccount->account_number }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Daily Limit: </span>
                        <span class="detail-value">${{ number_format($card->daily_limit, 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Contactless: </span>
                        <span class="detail-value">
                            <span class="status-badge {{ $card->is_contactless_enabled ? 'success' : 'danger' }}">
                                {{ $card->is_contactless_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status: </span>
                        <span class="detail-value">
                            <span class="status-badge {{ $card->isActive() ? 'success' : 'danger' }}">
                                {{ ucfirst($card->card_status) }}
                            </span>
                        </span>
                    </div>
                </div>

                <!-- Card Actions -->
                <div class="card-actions gap-2">
                    <button wire:click="toggleCardStatus({{ $card->id }})"
                        class="act-btn {{ $card->isActive() ? 'danger' : 'success' }}" wire:loading.attr="disabled">
                        <i class="bi bi-{{ $card->isActive() ? 'shield-slash' : 'shield-check' }}"></i>
                        {{ $card->isActive() ? 'Block Card' : 'Activate Card' }}
                    </button>
                    <button wire:click="selectCardForDelete({{ $card->id }})" class="act-btn danger"
                        wire:loading.attr="disabled">
                        <i class="bi bi-trash"></i>
                        Remove Card
                    </button>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="bi bi-credit-card"></i>
                <h3>No Cards Yet</h3>
                <p>Add your first card to get started</p>
                <button wire:click="openAddCardModal" class="btn-link btn">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        @endforelse
    </div>

    <!-- Add Card Modal -->
    <x-bottom-sheet id="showAddCardModal" title="Add New Card">
        <div class="add-card-form">
            <!-- Card Holder Name -->
            <div class="form-field">
                <label class="form-label">Card Holder Name</label>
                <input type="text" wire:model="cardHolderName" class="form-control" placeholder="John Doe">
                @error('cardHolderName')
                    <div class="text-danger text-xs">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Card Number -->
            <div class="form-field">
                <label class="form-label">Card Number</label>
                <input type="text" wire:model="cardNumber" class="form-control" placeholder="1234 5678 9012 3456"
                    maxlength="16">
                @error('cardNumber')
                    <div class="text-danger text-xs">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Expiry Date & CVV -->
            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Expiry Date</label>
                    <input type="month" wire:model="expiryDate" class="form-control">
                    @error('expiryDate')
                        <div class="text-danger text-xs">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="form-field">
                    <label class="form-label">CVV</label>
                    <input type="text" wire:model="cvv" class="form-control" placeholder="123" maxlength="3">
                    @error('cvv')
                        <div class="text-danger text-xs">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <!-- Card PIN -->
            <div class="form-field">
                <label class="form-label">Card PIN</label>
                <input type="password" wire:model="cardPin" class="form-control" placeholder="****" maxlength="4">
                @error('cardPin')
                    <div class="text-danger text-xs">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Link to Account -->
            <div class="form-field">
                <label class="form-label">Link to Account</label>
                <select wire:model="userAccountId" class="form-select">
                    <option value="">Select account...</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">
                            {{ $account->account_number }} ({{ $account->currency }}
                            {{ number_format($account->balance, 2) }})
                        </option>
                    @endforeach
                </select>
                @error('userAccountId')
                    <div class="text-danger text-xs">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Card Type -->
            <div class="form-field">
                <label class="form-label">Card Type</label>
                <select wire:model="cardType" class="form-select">
                    <option value="debit">Debit Card</option>
                    <option value="credit">Credit Card</option>
                </select>
            </div>

            <!-- Daily Limit -->
            <div class="form-field">
                <label class="form-label">Daily Spending Limit</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" wire:model="dailyLimit" class="form-control" placeholder="5000"
                        step="100">
                </div>
                @error('dailyLimit')
                    <div class="text-danger text-xs">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons gap-2">
                <button wire:click="closeAddCardModal" class="btn-cancel">
                    Cancel
                </button>
                <button wire:click="addCard" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="addCard">
                        <i class="bi bi-plus-circle"></i>
                        Add Card
                    </span>
                    <span wire:loading wire:target="addCard">
                        <x-spinner />
                        Adding...
                    </span>
                </button>
            </div>
        </div>
    </x-bottom-sheet>

    <!-- Delete Confirmation Modal -->
    <x-bottom-sheet id="showDeleteModal" title="Remove Card">
        <div class="delete-card-form">
            <div class="warning-box">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Are you sure?</strong>
                    <p>This action cannot be undone. Please enter your account password to confirm.</p>
                </div>
            </div>

            @if ($selectedCard)
                <div class="card-info-box">
                    <p><strong>Card:</strong> {{ $this->maskCardNumber($selectedCard->card_number) }}</p>
                    <p><strong>Holder:</strong> {{ $selectedCard->card_holder_name }}</p>
                </div>
            @endif

            <div class="form-field">
                <label class="form-label">Account Password</label>
                <input type="password" wire:model="deletePassword" class="form-control"
                    placeholder="Enter your password">
                @error('deletePassword')
                    <div class="text-danger text-xs">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="action-buttons gap-2">
                <button wire:click="closeDeleteModal" class="btn-cancel">
                    Cancel
                </button>
                <button wire:click="deleteCard" class="btn-danger" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="deleteCard">
                        <i class="bi bi-trash"></i>
                        Remove Card
                    </span>
                    <span wire:loading wire:target="deleteCard">
                        <x-spinner />
                        Removing...
                    </span>
                </button>
            </div>
        </div>
    </x-bottom-sheet>

    <livewire:mobile-app.component.bottom-nav />
</div>
