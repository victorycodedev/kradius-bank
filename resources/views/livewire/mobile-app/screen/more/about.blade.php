<div class="screen about-screen">
    <!-- Header -->
    <div class="investment-header">
        <x-link :href="route('more')" class="btn-back" icon="arrow-left" />
        <h1>About</h1>
    </div>

    <div class="about-container">
        <!-- App Logo Section -->
        <div class="app-logo-section">
            <div class="app-logo">
                <i class="bi bi-bank"></i>
            </div>
            <h2>{{ $appName }}</h2>
            <p class="version-text">Version {{ $appVersion }}</p>
        </div>

        <!-- App Description -->
        <div class="about-card">
            <h3 class="section-title">About {{ $appName }}</h3>
            <p class="about-description">
                {{ $appName }} is your trusted financial partner, providing secure and innovative banking
                solutions.
                We're committed to making your financial journey seamless with cutting-edge technology and
                exceptional customer service.
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
                        <span class="info-value">{{ $companyName }}</span>
                    </div>
                </div>

                @if ($companyAddress)
                    <div class="info-item">
                        <i class="bi bi-geo-alt"></i>
                        <div>
                            <span class="info-label">Address</span>
                            <span class="info-value">{{ $companyAddress }}</span>
                        </div>
                    </div>
                @endif

                @if ($companyPhone)
                    <div class="info-item">
                        <i class="bi bi-telephone"></i>
                        <div>
                            <span class="info-label">Phone</span>
                            <a href="tel:{{ $companyPhone }}" class="info-value link">{{ $companyPhone }}</a>
                        </div>
                    </div>
                @endif

                <div class="info-item">
                    <i class="bi bi-envelope"></i>
                    <div>
                        <span class="info-label">Support Email</span>
                        <a href="mailto:{{ $supportEmail }}" class="info-value link">{{ $supportEmail }}</a>
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
                    <strong>{{ $appVersion }}</strong>
                </div>
                <div class="system-item">
                    <span>Build Date</span>
                    <strong>{{ now()->format('F Y') }}</strong>
                </div>
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
                <a href="#" class="legal-link">
                    <span>Terms of Service</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="#" class="legal-link">
                    <span>Privacy Policy</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="#" class="legal-link">
                    <span>Licenses</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>

        <!-- Social Media -->
        <div class="about-card">
            <h3 class="section-title">Connect With Us</h3>
            <div class="social-links">
                <a href="#" class="social-btn">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="#" class="social-btn">
                    <i class="bi bi-twitter"></i>
                </a>
                <a href="#" class="social-btn">
                    <i class="bi bi-instagram"></i>
                </a>
                <a href="#" class="social-btn">
                    <i class="bi bi-linkedin"></i>
                </a>
            </div>
        </div>

        <!-- Copyright -->
        <div class="copyright-text">
            <p>&copy; {{ now()->year }} {{ $companyName }}. All rights reserved.</p>
        </div>
    </div>

    <livewire:mobile-app.component.bottom-nav />
</div>
