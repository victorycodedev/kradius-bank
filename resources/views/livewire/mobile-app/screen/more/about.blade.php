<div class="screen about-screen" x-data="{
    showTernsModal: $wire.entangle('showTernsModal'),
    showPrivacyModal: $wire.entangle('showPrivacyModal')
}">
    <!-- Header -->
    <div class="investment-header">
        <x-link :href="route('more')" class="btn-back" icon="arrow-left" />
        <h1>About</h1>
    </div>

    <div class="about-container">
        <!-- App Logo Section -->
        <div class="app-logo-section">
            <div class="app-logo">
                @if ($configuration->getFirstMediaUrl('mobile_app_icon', 'apple-touch-icon'))
                    <img src="{{ $configuration->getFirstMediaUrl('mobile_app_icon', 'apple-touch-icon') }}"
                        style="width:200px" class="img-fluid">
                @else
                    <i class="bi bi-bank"></i>
                @endif
            </div>
            <h2>{{ $configuration->app_name }}</h2>
            <p class="version-text">Version {{ $configuration->app_version }}</p>
        </div>

        <!-- App Description -->
        <div class="about-card">
            <h3 class="section-title">About {{ $configuration->app_name }}</h3>
            <p class="about-description">
                {{ $configuration->about }}
            </p>
        </div>

        <!-- Company Information -->
        <div class="about-card">
            <h3 class="section-title">Company Information</h3>
            <div class="info-list">
                <div class="info-item">
                    <i class="bi bi-building"></i>
                    <div>
                        <span class="info-label">Company Name</span>
                        <span class="info-value">{{ $configuration->app_name }}</span>
                    </div>
                </div>

                @if ($configuration->support_address)
                    <div class="info-item">
                        <i class="bi bi-geo-alt"></i>
                        <div>
                            <span class="info-label">Address</span>
                            <span class="info-value">{{ $configuration->support_address }}</span>
                        </div>
                    </div>
                @endif

                @if ($configuration->support_phone)
                    <div class="info-item">
                        <i class="bi bi-telephone"></i>
                        <div>
                            <span class="info-label">Phone</span>
                            <a href="tel:{{ $configuration->support_phone }}"
                                class="info-value link">{{ $configuration->support_phone }}</a>
                        </div>
                    </div>
                @endif

                <div class="info-item">
                    <i class="bi bi-envelope"></i>
                    <div>
                        <span class="info-label">Support Email</span>
                        <a href="mailto:{{ $configuration->support_email }}"
                            class="info-value link">{{ $configuration->support_email }}</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- App Features -->
        <div class="about-card">
            <h3 class="section-title">Features</h3>
            <div class="features-grid">
                <div class="feature-item">
                    <i class="bi bi-shield-check"></i>
                    <span>Secure Banking</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-lightning-charge"></i>
                    <span>Fast Transfers</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-credit-card"></i>
                    <span>Card Management</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-graph-up"></i>
                    <span>Investments</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-cash-coin"></i>
                    <span>Loans</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-phone"></i>
                    <span>24/7 Support</span>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="about-card">
            <h3 class="section-title">System Information</h3>
            <div class="system-info">
                <div class="system-item">
                    <span>App Version</span>
                    <strong>{{ $configuration->app_version }}</strong>
                </div>
                {{-- <div class="system-item">
                    <span>Build Date</span>
                    <strong>{{ now()->format('F Y') }}</strong>
                </div> --}}
                <div class="system-item">
                    <span>Platform</span>
                    <strong>Web & Mobile</strong>
                </div>
            </div>
        </div>

        <!-- Legal Links -->
        <div class="about-card">
            <h3 class="section-title">Legal</h3>
            <div class="legal-links">
                <a href="#" @click.prevent="showTernsModal = true" class="legal-link">
                    <span>Terms of Service</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="#" @click.prevent="showPrivacyModal = true" class="legal-link">
                    <span>Privacy Policy</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        @if (filled($configuration->facebook_url) ||
                filled($configuration->twitter_url) ||
                filled($configuration->instagram_url) ||
                filled($configuration->linkedin_url))
            <!-- Social Media -->
            <div class="about-card">
                <h3 class="section-title">Connect With Us</h3>
                <div class="social-links">
                    @if ($configuration->facebook_url)
                        <a href="{{ $configuration->facebook_url }}" class="social-btn" target="_blank">
                            <i class="bi bi-facebook"></i>
                        </a>
                    @endif
                    @if ($configuration->twitter_url)
                        <a href="{{ $configuration->twitter_url }}" class="social-btn" target="_blank">
                            <i class="bi bi-twitter"></i>
                        </a>
                    @endif
                    @if ($configuration->instagram_url)
                        <a href="{{ $configuration->instagram_url }}" class="social-btn" target="_blank">
                            <i class="bi bi-instagram"></i>
                        </a>
                    @endif
                    @if ($configuration->linkedin_url)
                        <a href="{{ $configuration->linkedin_url }}" class="social-btn" target="_blank">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @if ($configuration->copyright_text)
            <div class="copyright-text">
                <p>{{ $configuration->copyright_text }}</p>
            </div>
        @endif
    </div>

    <x-bottom-sheet id="showTernsModal" title="Terms of Service">
        {!! str($configuration->terms_and_conditions)->sanitizeHtml() !!}
    </x-bottom-sheet>

    <x-bottom-sheet id="showPrivacyModal" title="Privacy Policy">
        {!! str($configuration->privacy_policy)->sanitizeHtml() !!}
    </x-bottom-sheet>

    <livewire:mobile-app.component.bottom-nav />
</div>
