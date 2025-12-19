<div class="screen">
    <div class="investment-header">
        <x-link :href="route('dashboard')" class="btn-back" icon="arrow-left" />
        <h1>Menu</h1>
    </div>

    <div class="menu-item-container">
        <!-- Item -->
        <a href="{{ route('account.profile') }}" class="menu-item text-decoration-none">
            <div class="menu-left">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
            </div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>
        @if (Auth::user()->see_their_cards)
            <a href="{{ route('account.cards') }}" class="menu-item text-decoration-none">
                <div class="menu-left">
                    <i class="bi bi-credit-card"></i>
                    <span>My Cards</span>
                </div>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>
        @endif
        @if (Auth::user()->see_their_beneficiaries)
            <a href="{{ route('account.beneficiaries') }}" class="menu-item text-decoration-none">
                <div class="menu-left">
                    <i class="bi bi-people-fill"></i>
                    <span>Beneficiaries</span>
                </div>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>
        @endif

        <a href="{{ route('account.kyc') }}" class="menu-item text-decoration-none">
            <div class="menu-left">
                <i class="bi bi-person-vcard-fill"></i>
                <span>KYC</span>
            </div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>

        <a href="{{ route('account.security') }}" class="menu-item text-decoration-none">
            <div class="menu-left">
                <i class="bi bi-shield-shaded"></i>
                <span>Security</span>
            </div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>

        <a href="{{ route('account.faqs-and-support') }}" class="menu-item text-decoration-none">
            <div class="menu-left">
                <i class="bi bi-chat-dots"></i>
                <span>FAQs and Support</span>
            </div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>

        <a href="{{ route('account.about') }}" class="menu-item text-decoration-none">
            <div class="menu-left">
                <i class="bi bi-info-circle"></i>
                <span>About</span>
            </div>
            <i class="bi bi-chevron-right menu-arrow"></i>
        </a>

        <div class="mt-2">
            <form method="POST" action="{{ route('logout') }}" id="logout">
                @csrf
                <div class="d-grid gap-2 text-center">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout').submit()"
                        class="btn-danger text-decoration-none btn-block text-center">
                        <i class="bi bi-box-arrow-right"></i>
                        Logout
                    </a>
                </div>
            </form>
        </div>
    </div>
    <livewire:mobile-app.component.bottom-nav />
</div>
