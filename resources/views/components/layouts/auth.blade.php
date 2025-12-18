<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    @PwaHead
    <title>Banking App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('mobileui/style.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.2/dist/cdn.min.js"></script>
</head>

<body>

    <div id="mobile-only-message" style="display:none;">
        <div class="mobile-warning">
            <h2>Mobile Device Required</h2>
            <p>This app is only available on mobile phones. Please open it on a mobile device.</p>
        </div>
    </div>

    {{-- <div :class="darkMode ? 'dark-mode' : ''" class="app-container" id="app-wrapper"> --}}
    <div x-data="bankingApp()" :class="darkMode ? 'dark-mode' : ''" class="app-container" id="app-wrapper">
        {{ $slot }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
    <script>
        function checkDevice() {
            const isMobile = window.innerWidth <= 430;

            if (!isMobile) {
                // Show warning, hide app
                document.getElementById("mobile-only-message").style.display = "block";
                document.getElementById("app-wrapper").style.display = "none";
                document.body.style.background = "#fff";
            } else {
                // Show app
                document.getElementById("mobile-only-message").style.display = "none";
                document.getElementById("app-wrapper").style.display = "block";
            }
        }

        // Run initially
        checkDevice();

        // Run on resize (if someone rotates or resizes window)
        window.addEventListener("resize", checkDevice);
    </script>
    <script src="{{ asset('mobileui/scripts.js') }}"></script>
    @RegisterServiceWorkerScript
</body>

</html>
