<div class="screen profile-screen">
    <!-- Header -->
    <div class="investment-header">
        <x-link :href="route('more')" class="btn-back" icon="arrow-left" />
        <h1>My Profile</h1>
    </div>

    <!-- Profile Form -->
    <div class="profile-container">
        <!-- Profile Avatar Section -->
        <div class="profile-avatar-section">
            @if ($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="" class="profile-avatar">
            @else
                <div class="profile-avatar">
                    <span class="avatar-text">{{ Auth::user()->initials() }}</span>
                </div>
            @endif
            <h2>{{ Auth::user()->name }}</h2>
            <p>{{ Auth::user()->email }}</p>
        </div>

        <!-- Personal Information -->
        <div class="profile-section">
            <h3 class="section-title">Personal Information</h3>

            <div class="form-field">
                <label class="form-label">Full Name *</label>
                <input type="text" wire:model="name" class="form-control" placeholder="John Doe">
                @error('name')
                    <div class="text-danger text-xs">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-field">
                <label class="form-label">Email Address</label>
                <input type="email" wire:model="email" class="form-control" placeholder="john@example.com" readonly
                    disabled>
                <small class="form-hint">Email cannot be changed</small>
            </div>

            <div class="form-field">
                <label class="form-label">Phone Number</label>
                <input type="tel" wire:model="phone" class="form-control" placeholder="+1 (555) 000-0000">
                @error('phone')
                    <div class="text-danger text-xs">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-field">
                <label class="form-label">Date of Birth</label>
                <input type="date" wire:model="date_of_birth" class="form-control">
                @error('date_of_birth')
                    <div class="text-danger text-xs">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <!-- Address Information -->
        <div class="profile-section">
            <h3 class="section-title">Address Information</h3>

            <div class="form-field">
                <label class="form-label">Street Address</label>
                <input type="text" wire:model="address" class="form-control" placeholder="123 Main Street">
                @error('address')
                    <div class="text-danger text-xs">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">City</label>
                    <input type="text" wire:model="city" class="form-control" placeholder="New York">
                    @error('city')
                        <div class="text-danger text-xs">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-field">
                    <label class="form-label">State</label>
                    <input type="text" wire:model="state" class="form-control" placeholder="NY">
                    @error('state')
                        <div class="text-danger text-xs">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Country</label>
                    <input type="text" wire:model="country" class="form-control" placeholder="United States">
                    @error('country')
                        <div class="text-danger text-xs">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-field">
                    <label class="form-label">Zip Code</label>
                    <input type="text" wire:model="zip_code" class="form-control" placeholder="10001">
                    @error('zip_code')
                        <div class="text-danger text-xs">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="profile-actions">
            <button wire:click="updateProfile" class="btn-save-profile" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="updateProfile">
                    <i class="bi bi-check-circle"></i>
                    Save Changes
                </span>
                <span wire:loading wire:target="updateProfile">
                    <x-spinner />
                    Saving...
                </span>
            </button>
        </div>
    </div>

    <livewire:mobile-app.component.bottom-nav />
</div>
