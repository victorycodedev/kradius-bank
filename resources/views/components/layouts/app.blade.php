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
    <meta name="apple-mobile-web-app-title" content="{{ $configuration->app_short_name ?? 'BankApp' }}">

    <!-- Apple Touch Icons -->
    @if ($configuration->getFirstMediaUrl('mobile_app_icon', 'apple-touch-icon'))
        <link rel="apple-touch-icon"
            href="{{ $configuration->getFirstMediaUrl('mobile_app_icon', 'apple-touch-icon') }}">
    @endif

    <!-- iOS Splash Screens -->
    @if ($configuration->hasMedia('splash_screen'))
        <link rel="apple-touch-startup-image"
            href="{{ $configuration->getFirstMediaUrl('splash_screen', 'splash-640x1136') }}"
            media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
        <link rel="apple-touch-startup-image"
            href="{{ $configuration->getFirstMediaUrl('splash_screen', 'splash-750x1334') }}"
            media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
        <link rel="apple-touch-startup-image"
            href="{{ $configuration->getFirstMediaUrl('splash_screen', 'splash-1242x2208') }}"
            media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
        <link rel="apple-touch-startup-image"
            href="{{ $configuration->getFirstMediaUrl('splash_screen', 'splash-1125x2436') }}"
            media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
        <link rel="apple-touch-startup-image"
            href="{{ $configuration->getFirstMediaUrl('splash_screen', 'splash-1242x2688') }}"
            media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
        <link rel="apple-touch-startup-image"
            href="{{ $configuration->getFirstMediaUrl('splash_screen', 'splash-828x1792') }}"
            media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
    @endif

    <!-- Android Chrome Theme -->
    <meta name="theme-color" content="{{ $configuration->primary_color ?? '#f46b10' }}">
    <meta name="mobile-web-app-capable" content="yes">

    <title>{{ config('app.name') }} - {{ $title }}</title>

    <!-- Preload Critical Resources -->
    <link rel="preload" href="{{ asset('mobileui/style.css') }}" as="style">
    <link rel="preload" href="{{ asset('mobileui/scripts.js') }}" as="script">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('mobileui/style.css') }}">

    <style>
        :root {
            --bs-primary: {{ $configuration->primary_color }};
            --primary-color: {{ $configuration->primary_color }};
            --secondary-color: {{ $configuration->primary_color }};
            --accent-color: {{ $configuration->accent_color }};
            --bg-secondary: {{ $configuration->primary_color }};
            --text-secondary: {{ $configuration->primary_color }};
        }

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

        /* Offline Banner */
        .offline-banner {
            position: fixed;
            top: env(safe-area-inset-top, 0);
            left: 0;
            right: 0;
            background: #ff3b30;
            color: white;
            padding: 0.75rem;
            text-align: center;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            animation: slideDown 0.3s ease;
            font-size: 0.9rem;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
            }

            to {
                transform: translateY(0);
            }
        }

        /* Install Banner */
        .install-banner {
            position: fixed;
            bottom: calc(80px + env(safe-area-inset-bottom, 0));
            left: 1rem;
            right: 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1000;
            animation: slideUp 0.3s ease;
        }

        .install-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .install-content i {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .install-content strong {
            display: block;
            font-size: 0.95rem;
        }

        .install-content p {
            margin: 0;
            font-size: 0.8rem;
            color: #666;
        }

        .btn-install {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            white-space: nowrap;
        }

        .btn-close-banner {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #999;
            padding: 0;
            width: 30px;
            height: 30px;
            cursor: pointer;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
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

    @stack('styles')
</head>

<body>
    <!-- Desktop Warning Only -->
    <div id="mobile-only-message" style="display:none;">
        <div class="mobile-warning">
            <div class="warning-icon">
                <i class="bi bi-phone"></i>
            </div>
            <h2>Mobile Device Required</h2>
            <p>This app is only available on mobile phones. Please open it on a mobile device.</p>
        </div>
    </div>

    <div x-data="bankingApp()" :class="darkMode ? 'dark-mode' : ''" class="app-container" id="app-wrapper">
        {{ $slot }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>

    @flasher_render()
    <script defer src="{{ asset('mobileui/scripts.js') }}"></script>

    @stack('scripts')
    @RegisterServiceWorkerScript
</body>

</html>
