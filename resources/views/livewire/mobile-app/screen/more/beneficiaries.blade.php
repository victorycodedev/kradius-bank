<div class="screen beneficiaries-screen">
    <!-- Header -->
    <div class="investment-header">
        <x-link :href="route('more')" class="btn-back" icon="arrow-left" />
        <h1>Beneficiaries</h1>
        @if (Auth::user()->can_add_beneficiary)
            <button wire:click="openAddModal" class="add-card-btn">
                <i class="bi bi-plus-lg"></i>
            </button>
        @endif
    </div>

    <!-- Beneficiaries List -->
    <div class="beneficiaries-container">
        @forelse($beneficiaries as $beneficiary)
            <div class="beneficiary-card" wire:key="ben-{{ $beneficiary->id }}">
                <div class="beneficiary-header">
                    <div class="beneficiary-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="beneficiary-info">
                        <h4>{{ $beneficiary->nickname ?: $beneficiary->account_name }}</h4>
                        <p>{{ $beneficiary->account_name }}</p>
                    </div>
                    <button wire:click="toggleFavorite({{ $beneficiary->id }})" class="favorite-btn"
                        wire:loading.attr="disabled">
                        <i class="bi bi-star{{ $beneficiary->is_favorite ? '-fill' : '' }}"></i>
                    </button>
                </div>

                <div class="beneficiary-details">
                    <div class="detail-row">
                        <span class="detail-label">
                            <i class="bi bi-bank"></i>
                            Bank
                        </span>
                        <span class="detail-value">{{ $beneficiary->bank_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">
                            <i class="bi bi-credit-card"></i>
                            Account
                        </span>
                        <span class="detail-value">{{ $beneficiary->account_number }}</span>
                    </div>
                    @if ($beneficiary->bank_code)
                        <div class="detail-row">
                            <span class="detail-label">
                                <i class="bi bi-hash"></i>
                                Bank Code
                            </span>
                            <span class="detail-value">{{ $beneficiary->bank_code }}</span>
                        </div>
                    @endif
                </div>
                @if (Auth::user()->can_manage_beneficiary)
                    <div class="beneficiary-actions">
                        <button wire:click="selectBeneficiaryForDelete({{ $beneficiary->id }})"
                            class="action-btn text-danger" wire:loading.attr="disabled">
                            <i class="bi bi-trash"></i>
                            Remove
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h3>No Beneficiaries Yet</h3>
                <p>Add your first beneficiary for quick transfers</p>
                <button wire:click="openAddModal" class="btn-link btn">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        @endforelse
    </div>

    <!-- Add Beneficiary Modal -->
    <x-bottom-sheet id="showAddModal" title="Add Beneficiary">
        <div class="add-beneficiary-form">
            <!-- Account Number -->
            <div class="form-group">
                <label class="form-label">Account Number *</label>
                <input type="text" wire:model="accountNumber" class="form-input" placeholder="1234567890">
                @error('accountNumber')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Account Name -->
            <div class="form-group">
                <label class="form-label">Account Name *</label>
                <input type="text" wire:model="accountName" class="form-input" placeholder="John Doe">
                @error('accountName')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Bank Name -->
            <div class="form-group">
                <label class="form-label">Bank Name *</label>
                <input type="text" wire:model="bankName" class="form-input" placeholder="First Bank">
                @error('bankName')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Bank Code -->
            <div class="form-group">
                <label class="form-label">Bank Code (Optional)</label>
                <input type="text" wire:model="bankCode" class="form-input" placeholder="011">
                @error('bankCode')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Nickname -->
            <div class="form-group">
                <label class="form-label">Nickname (Optional)</label>
                <input type="text" wire:model="nickname" class="form-input"
                    placeholder="Mom, Dad, Best Friend, etc.">
                <small class="form-hint">Give this beneficiary a friendly name for easy identification</small>
                @error('nickname')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>
            {{-- 
            <!-- Favorite Toggle -->
            <div class="form-group mt-3">
                <label class="checkbox-label">
                    <input type="checkbox" wire:model="isFavorite" class="form-checkbox">
                    <span>Add to favorites</span>
                </label>
            </div> --}}

            <!-- Action Buttons -->
            <div class="action-buttons gap-2">
                <button wire:click="closeAddModal" class="btn-cancel">
                    Cancel
                </button>
                <button wire:click="addBeneficiary" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="addBeneficiary">
                        <i class="bi bi-plus-circle"></i>
                        Add Beneficiary
                    </span>
                    <span wire:loading wire:target="addBeneficiary">
                        <x-spinner />
                        Adding...
                    </span>
                </button>
            </div>
        </div>
    </x-bottom-sheet>

    <!-- Delete Confirmation Modal -->
    <x-bottom-sheet id="showDeleteModal" title="Remove Beneficiary">
        <div class="delete-beneficiary-form">
            <div class="warning-box">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    <strong>Are you sure?</strong>
                    <p>This action cannot be undone. Please enter your account password to confirm.</p>
                </div>
            </div>

            @if ($selectedBeneficiary)
                <div class="card-info-box">
                    <p><strong>Name:</strong>
                        {{ $selectedBeneficiary->nickname ?: $selectedBeneficiary->account_name }}</p>
                    <p><strong>Account:</strong> {{ $selectedBeneficiary->account_number }}</p>
                    <p><strong>Bank:</strong> {{ $selectedBeneficiary->bank_name }}</p>
                </div>
            @endif

            <div class="form-group">
                <label class="form-label">Account Password</label>
                <input type="password" wire:model="deletePassword" class="form-input"
                    placeholder="Enter your password">
                @error('deletePassword')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="action-buttons gap-2">
                <button wire:click="closeDeleteModal" class="btn-cancel">
                    Cancel
                </button>
                <button wire:click="deleteBeneficiary" class="btn-danger" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="deleteBeneficiary">
                        <i class="bi bi-trash"></i>
                        Remove Beneficiary
                    </span>
                    <span wire:loading wire:target="deleteBeneficiary">
                        <x-spinner />
                        Removing...
                    </span>
                </button>
            </div>
        </div>
    </x-bottom-sheet>

    <livewire:mobile-app.component.bottom-nav />
</div>
