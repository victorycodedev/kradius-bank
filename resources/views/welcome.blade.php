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
    <meta name="apple-mobile-web-app-title" content="{{ $configuration->app_short_name ?? config('app.name') }}">

    <!-- Apple Touch Icons -->
    @if ($configuration->getFirstMediaUrl('mobile_app_icon', 'apple-touch-icon'))
        <link rel="apple-touch-icon"
            href="{{ $configuration->getFirstMediaUrl('mobile_app_icon', 'apple-touch-icon') }}">
        <link rel="shortcut icon" href="{{ $configuration->getFirstMediaUrl('mobile_app_icon', 'apple-touch-icon') }}">
    @endif

    <!-- Android Chrome Theme -->
    <meta name="theme-color" content="{{ $configuration->primary_color ?? '#f46b10' }}">
    <meta name="mobile-web-app-capable" content="yes">

    <title>{{ config('app.name') }} - Get the App</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('mobileui/style.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.2/dist/cdn.min.js"></script>

    <style>
        :root {
            --primary-color: {{ $configuration->primary_color ?? '#f46b10' }};
            --accent-color: {{ $configuration->accent_color ?? '#ff8c42' }};
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .welcome-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            position: relative;
        }

        /* Animated Background Shapes */
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        .bg-shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .bg-shape:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -50px;
            right: -50px;
            animation-delay: 5s;
        }

        .bg-shape:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
            animation-delay: 10s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
        }

        /* Content */
        .welcome-content {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 450px;
            width: 100%;
        }

        .app-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: white;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: bounceIn 1s ease-out;
        }

        .app-logo img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .app-logo i {
            font-size: 4rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0) rotate(-180deg);
                opacity: 0;
            }

            50% {
                transform: scale(1.1) rotate(10deg);
            }

            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        .welcome-content h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .welcome-content p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.1rem;
            margin-bottom: 3rem;
            line-height: 1.6;
            text-shadow: 0 1px 10px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Install Buttons */
        .install-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .install-btn {
            background: white;
            color: var(--primary-color);
            border: none;
            padding: 1rem 2rem;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .install-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.05);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .install-btn:active::before {
            width: 300px;
            height: 300px;
        }

        .install-btn:active {
            transform: scale(0.97);
        }

        .install-btn i {
            font-size: 1.5rem;
        }

        .install-btn.android {
            background: white;
        }

        .install-btn.ios {
            background: rgba(255, 255, 255, 0.95);
        }

        .install-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* iOS Instructions Slide */
        .ios-instructions {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-radius: 30px 30px 0 0;
            padding: 2rem 1.5rem;
            max-height: 70vh;
            overflow-y: auto;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.3);
            transform: translateY(100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
        }

        .ios-instructions.show {
            transform: translateY(0);
        }

        .ios-instructions-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .ios-instructions-header h3 {
            font-size: 1.3rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .close-instructions {
            background: #f0f0f0;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .close-instructions:active {
            transform: scale(0.9);
            background: #e0e0e0;
        }

        .ios-instructions ol {
            list-style: none;
            counter-reset: step-counter;
            padding: 0;
        }

        .ios-instructions li {
            counter-increment: step-counter;
            position: relative;
            padding: 1.25rem 1.25rem 1.25rem 4rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            line-height: 1.6;
            color: #555;
        }

        .ios-instructions li::before {
            content: counter(step-counter);
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .ios-instructions li:last-child {
            margin-bottom: 0;
        }

        .ios-instructions strong {
            color: #333;
            font-weight: 600;
        }

        .ios-instructions i.bi {
            font-size: 1.1rem;
            vertical-align: middle;
            margin: 0 0.25rem;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
        }

        .overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Browser Login Link */
        .browser-login {
            margin-top: 2rem;
            animation: fadeInUp 1s ease-out 0.8s both;
        }

        .browser-login a {
            color: white;
            text-decoration: none;
            font-size: 0.95rem;
            opacity: 0.9;
            transition: opacity 0.2s;
        }

        .browser-login a:hover {
            opacity: 1;
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 430px) {
            .welcome-content h1 {
                font-size: 2rem;
            }

            .welcome-content p {
                font-size: 1rem;
            }

            .app-logo {
                width: 100px;
                height: 100px;
            }

            .app-logo img {
                width: 70px;
                height: 70px;
            }

            .app-logo i {
                font-size: 3.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="welcome-container" x-data="welcomeApp()">
        <!-- Animated Background -->
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>

        <!-- Main Content -->
        <div class="welcome-content">
            <!-- App Logo -->
            <div class="app-logo">
                @if ($configuration->getFirstMediaUrl('mobile_app_icon', 'icon-192x192'))
                    <img src="{{ $configuration->getFirstMediaUrl('mobile_app_icon', 'icon-192x192') }}"
                        alt="{{ config('app.name') }}">
                @else
                    <i class="bi bi-wallet2"></i>
                @endif
            </div>

            <!-- Heading -->
            <h1>{{ $configuration->app_name ?? config('app.name') }}</h1>
            <p>{{ $configuration->app_slogan ?? 'Your secure mobile banking companion. Install now for the best experience.' }}
            </p>

            <!-- Install Buttons -->
            <div class="install-buttons">
                <!-- Android Install Button -->
                <button class="install-btn android" @click="installAndroid()" x-show="isAndroid"
                    :disabled="isInstalling">
                    <i class="bi bi-google-play"></i>
                    <span x-text="isInstalling ? 'Installing...' : 'Install on Android'"></span>
                </button>

                <!-- iOS Install Button -->
                <button class="install-btn ios" @click="showIOSInstructions = true" x-show="isIOS">
                    <i class="bi bi-apple"></i>
                    <span>Install on iPhone</span>
                </button>

                <!-- Generic Install Button (for other browsers) -->
                <button class="install-btn" @click="installGeneric()" x-show="!isAndroid && !isIOS && canInstall"
                    :disabled="isInstalling">
                    <i class="bi bi-download"></i>
                    <span x-text="isInstalling ? 'Installing...' : 'Install App'"></span>
                </button>
            </div>

            <!-- Browser Login Link (Optional) -->
            <div class="browser-login">
                <a href="{{ route('login') }}">Continue in browser instead</a>
            </div>
        </div>

        <!-- iOS Instructions Overlay -->
        <div class="overlay" :class="{ 'show': showIOSInstructions }" @click="showIOSInstructions = false"></div>

        <!-- iOS Instructions Slide -->
        <div class="ios-instructions" :class="{ 'show': showIOSInstructions }">
            <div class="ios-instructions-header">
                <h3>
                    <i class="bi bi-apple"></i>
                    How to Install on iPhone
                </h3>
                <button class="close-instructions" @click="showIOSInstructions = false">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <ol>
                <li>
                    Tap the <strong>Share</strong> button
                    <i class="bi bi-box-arrow-up"></i>
                    at the bottom of Safari
                </li>
                <li>
                    Scroll down and tap
                    <strong>"Add to Home Screen"</strong>
                </li>
                <li>
                    Tap <strong>"Add"</strong> in the top right corner
                </li>
                <li>
                    Find the app icon on your home screen and tap to open
                </li>
            </ol>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>

    <script>
        function welcomeApp() {
            return {
                showIOSInstructions: false,
                isInstalling: false,
                deferredPrompt: null,
                isAndroid: false,
                isIOS: false,
                canInstall: false,

                init() {
                    // Detect platform
                    const userAgent = navigator.userAgent || navigator.vendor || window.opera;
                    this.isAndroid = /android/i.test(userAgent);
                    this.isIOS = /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream;

                    // Listen for install prompt
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.deferredPrompt = e;
                        this.canInstall = true;
                    });

                    // Check if already installed
                    if (this.isAlreadyInstalled()) {
                        window.location.href = '{{ route('login') }}';
                    }

                    // Add haptic feedback
                    this.addHapticFeedback();
                },

                isAlreadyInstalled() {
                    return window.matchMedia('(display-mode: standalone)').matches ||
                        window.navigator.standalone ||
                        document.referrer.includes('android-app://');
                },

                async installAndroid() {
                    if (!this.deferredPrompt) {
                        alert('Install prompt not available. Please use the browser menu to install.');
                        return;
                    }

                    this.isInstalling = true;
                    this.haptic('medium');

                    this.deferredPrompt.prompt();
                    const {
                        outcome
                    } = await this.deferredPrompt.userChoice;

                    console.log(`Install outcome: ${outcome}`);

                    if (outcome === 'accepted') {
                        this.haptic('success');
                        setTimeout(() => {
                            window.location.href = '{{ route('login') }}';
                        }, 1000);
                    } else {
                        this.isInstalling = false;
                        this.haptic('light');
                    }

                    this.deferredPrompt = null;
                },

                async installGeneric() {
                    if (!this.deferredPrompt) {
                        alert('Install not available. Please check your browser settings.');
                        return;
                    }

                    this.isInstalling = true;
                    this.haptic('medium');

                    this.deferredPrompt.prompt();
                    const {
                        outcome
                    } = await this.deferredPrompt.userChoice;

                    if (outcome === 'accepted') {
                        this.haptic('success');
                        setTimeout(() => {
                            window.location.href = '{{ route('login') }}';
                        }, 1000);
                    } else {
                        this.isInstalling = false;
                    }

                    this.deferredPrompt = null;
                },

                haptic(type = 'light') {
                    if (window.navigator && window.navigator.vibrate) {
                        switch (type) {
                            case 'light':
                                window.navigator.vibrate(10);
                                break;
                            case 'medium':
                                window.navigator.vibrate(20);
                                break;
                            case 'success':
                                window.navigator.vibrate([10, 50, 10]);
                                break;
                        }
                    }
                },

                addHapticFeedback() {
                    document.addEventListener('click', (e) => {
                        const button = e.target.closest('button, a');
                        if (button) {
                            this.haptic('light');
                        }
                    });
                }
            }
        }
    </script>

    @RegisterServiceWorkerScript
</body>

</html>
