<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">

    <!-- PWA Meta Tags -->
    @PwaHead

    <!-- iOS Specific -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">

    <!-- Apple Touch Icons -->
    @if ($configuration->getFirstMediaUrl('mobile_app_icon', 'apple-touch-icon'))
        <link rel="apple-touch-icon"
            href="{{ $configuration->getFirstMediaUrl('mobile_app_icon', 'apple-touch-icon') }}">
    @endif

    <!-- Android Chrome Theme -->
    <meta name="theme-color" content="{{ $configuration->primary_color ?? '#f46b10' }}">
    <meta name="mobile-web-app-capable" content="yes">

    <title>{{ config('app.name') }}</title>

    <!-- Preload Critical Resources -->
    <link rel="preload" href="{{ asset('mobileui/style.css') }}" as="style">
    <link rel="preload" href="{{ asset('mobileui/scripts.js') }}" as="script">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('mobileui/style.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.2/dist/cdn.min.js"></script>

    <style>
        /* Safe Area Support */
        body {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
            padding-left: env(safe-area-inset-left);
            padding-right: env(safe-area-inset-right);
        }

        /* Disable Pull-to-Refresh */
        body {
            overscroll-behavior-y: contain;
            -webkit-overflow-scrolling: touch;
        }

        /* Prevent default touch behaviors */
        * {
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
        }

        input,
        textarea,
        select {
            -webkit-touch-callout: default;
            -webkit-user-select: text;
            user-select: text;
        }

        /* Warning Messages Styling */
        .mobile-warning {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            text-align: center;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .warning-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .warning-icon i {
            font-size: 2.5rem;
            color: white;
        }

        .mobile-warning h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .mobile-warning p {
            color: #666;
            font-size: 1rem;
            margin-bottom: 0;
        }

        /* PWA Warning Specific */
        .pwa-warning {
            max-width: 600px;
            padding: 2rem 1.5rem;
        }

        .pwa-warning .warning-icon {
            background: linear-gradient(135deg, #f46b10 0%, #ff8c42 100%);
        }

        .install-instructions {
            margin-top: 2rem;
            text-align: left;
        }

        .instruction-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .instruction-card:last-child {
            margin-bottom: 0;
        }

        .instruction-card h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .instruction-card h3 i {
            font-size: 1.3rem;
        }

        .instruction-card ol {
            margin: 0;
            padding-left: 1.5rem;
        }

        .instruction-card li {
            margin-bottom: 0.75rem;
            color: #555;
            line-height: 1.6;
        }

        .instruction-card li:last-child {
            margin-bottom: 0;
        }

        .instruction-card strong {
            color: #333;
            font-weight: 600;
        }

        .instruction-card i.bi {
            font-size: 0.9rem;
        }

        @media (max-width: 430px) {
            .mobile-warning {
                margin: 1rem;
                padding: 1.5rem;
            }

            .instruction-card {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Desktop Warning -->
    <div id="mobile-only-message" style="display:none;">
        <div class="mobile-warning">
            <div class="warning-icon">
                <i class="bi bi-phone"></i>
            </div>
            <h2>Mobile Device Required</h2>
            <p>This app is only available on mobile phones. Please open it on a mobile device.</p>
        </div>
    </div>

    <!-- PWA Installation Required Warning -->
    <div id="pwa-required-message" style="display:none;">
        <div class="mobile-warning pwa-warning">
            <div class="warning-icon">
                <i class="bi bi-download"></i>
            </div>
            <h2>Installation Required</h2>
            <p>To use this app, you must install it on your device.</p>

            <div class="install-instructions">
                <div class="instruction-card">
                    <h3><i class="bi bi-apple"></i> iOS (Safari)</h3>
                    <ol>
                        <li>Tap the <strong>Share</strong> button <i class="bi bi-box-arrow-up"></i></li>
                        <li>Scroll down and tap <strong>"Add to Home Screen"</strong></li>
                        <li>Tap <strong>"Add"</strong> in the top right</li>
                        <li>Open the app from your home screen</li>
                    </ol>
                </div>

                <div class="instruction-card">
                    <h3><i class="bi bi-google"></i> Android (Chrome)</h3>
                    <ol>
                        <li>Tap the <strong>Menu</strong> button <i class="bi bi-three-dots-vertical"></i></li>
                        <li>Tap <strong>"Add to Home screen"</strong> or <strong>"Install app"</strong></li>
                        <li>Tap <strong>"Install"</strong> or <strong>"Add"</strong></li>
                        <li>Open the app from your home screen</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div x-data="bankingApp()" :class="darkMode ? 'dark-mode' : ''" class="app-container" id="app-wrapper"
        x-init="checkDeviceAuth()" style="display:none;">
        {{ $slot }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>

    @if (false)
        <script>
            // Check device and PWA status for auth pages
            function isPWA() {
                return window.matchMedia('(display-mode: standalone)').matches ||
                    window.navigator.standalone ||
                    document.referrer.includes('android-app://');
            }

            function checkDeviceAuth() {
                const isMobile = window.innerWidth <= 430;
                const isInstalledPWA = isPWA();

                // If not mobile device
                if (!isMobile) {
                    document.getElementById("mobile-only-message").style.display = "block";
                    document.getElementById("pwa-required-message").style.display = "none";
                    document.getElementById("app-wrapper").style.display = "none";
                    document.body.style.background = "#fff";
                    return;
                }

                // If mobile but not installed as PWA
                if (isMobile && !isInstalledPWA) {
                    document.getElementById("pwa-required-message").style.display = "block";
                    document.getElementById("mobile-only-message").style.display = "none";
                    document.getElementById("app-wrapper").style.display = "none";
                    document.body.style.background = "#fff";
                    return;
                }

                // Mobile and installed as PWA - show app
                document.getElementById("mobile-only-message").style.display = "none";
                document.getElementById("pwa-required-message").style.display = "none";
                document.getElementById("app-wrapper").style.display = "block";
            }

            checkDeviceAuth();
            window.addEventListener("resize", checkDeviceAuth);
        </script>
    @endif


    <script src="{{ asset('mobileui/scripts.js') }}"></script>
    @RegisterServiceWorkerScript
</body>

</html>
