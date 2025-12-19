// ========================================
// HAPTIC FEEDBACK
// ========================================
function hapticFeedback(type = "light") {
    if (window.navigator && window.navigator.vibrate) {
        switch (type) {
            case "light":
                window.navigator.vibrate(10);
                break;
            case "medium":
                window.navigator.vibrate(20);
                break;
            case "heavy":
                window.navigator.vibrate(50);
                break;
            case "success":
                window.navigator.vibrate([10, 50, 10]);
                break;
            case "error":
                window.navigator.vibrate([50, 100, 50]);
                break;
        }
    }
}

// Add haptic to all buttons globally
document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener(
        "click",
        function (e) {
            const button = e.target.closest(
                "button, .btn, a.button, .transaction-list-item"
            );
            if (button && !button.disabled) {
                hapticFeedback("light");
            }
        },
        { passive: true }
    );
});

// ========================================
// BALANCE SLIDER COMPONENT
// ========================================
function balanceSliderComponent(totalSlides) {
    return {
        currentSlide: 0,
        total: totalSlides,
        touchStartX: 0,
        touchEndX: 0,

        start(e) {
            if (this.total <= 1) return;
            this.touchStartX = e.touches[0].clientX;
            hapticFeedback("light");
        },

        move(e) {
            if (this.total <= 1) return;
            this.touchEndX = e.touches[0].clientX;
        },

        end() {
            if (this.total <= 1) return;

            const diff = this.touchStartX - this.touchEndX;
            const threshold = 50;

            if (diff > threshold && this.currentSlide < this.total - 1) {
                this.currentSlide++;
                hapticFeedback("medium");
            } else if (diff < -threshold && this.currentSlide > 0) {
                this.currentSlide--;
                hapticFeedback("medium");
            }

            this.touchStartX = 0;
            this.touchEndX = 0;
        },
    };
}

// ========================================
// NOTIFICATION SLIDER COMPONENT
// ========================================
function notificationSliderComponent(totalSlides, interval = 4000) {
    return {
        current: 0,
        total: totalSlides,
        touchStartX: 0,
        touchEndX: 0,
        timer: null,
        intervalTime: interval,

        init() {
            if (this.total > 1) {
                this.startAutoSlide();
            }
        },

        startAutoSlide() {
            this.stopAutoSlide();
            this.timer = setInterval(() => {
                this.next();
            }, this.intervalTime);
        },

        stopAutoSlide() {
            if (this.timer) clearInterval(this.timer);
        },

        resetTimer() {
            this.startAutoSlide();
        },

        next() {
            if (this.current < this.total - 1) {
                this.current++;
            } else {
                this.current = 0;
            }
        },

        start(e) {
            if (this.total <= 1) return;
            this.touchStartX = e.touches[0].clientX;
            this.stopAutoSlide();
        },

        move(e) {
            if (this.total <= 1) return;
            this.touchEndX = e.touches[0].clientX;
        },

        end() {
            if (this.total <= 1) return;

            const diff = this.touchStartX - this.touchEndX;
            const threshold = 50;

            if (diff > threshold) {
                this.next();
                hapticFeedback("light");
            } else if (diff < -threshold && this.current > 0) {
                this.current--;
                hapticFeedback("light");
            }

            this.touchStartX = 0;
            this.touchEndX = 0;
            this.resetTimer();
        },
    };
}

// ========================================
// LIVEWIRE HOOKS
// ========================================
document.addEventListener("livewire:init", () => {
    Livewire.hook("commit", ({ component, commit, respond, succeed, fail }) => {
        // PHPFlasher will automatically handle the notifications
    });
});

// ========================================
// DEVICE & PWA CHECK
// ========================================
function isPWA() {
    return (
        window.matchMedia("(display-mode: standalone)").matches ||
        window.navigator.standalone ||
        document.referrer.includes("android-app://")
    );
}

function checkDevice() {
    const isMobile = window.innerWidth <= 430;

    // For authenticated users (app.blade.php), only check if mobile
    if (!isMobile) {
        document.getElementById("mobile-only-message").style.display = "block";
        document.getElementById("app-wrapper").style.display = "none";
        document.body.style.background = "#fff";
        return;
    }

    // Mobile device - show app
    document.getElementById("mobile-only-message").style.display = "none";
    document.getElementById("app-wrapper").style.display = "block";
}

checkDevice();
window.addEventListener("resize", checkDevice);

// ========================================
// MAIN BANKING APP
// ========================================
function bankingApp() {
    return {
        onboardingSlide: 0,
        showPassword: false,
        showConfirmPassword: false,
        darkMode: false,

        init() {
            // Load saved dark mode state
            const savedDarkMode = localStorage.getItem("darkMode");
            if (savedDarkMode === "true") {
                this.darkMode = true;
            }

            // Prevent iOS bounce effect
            this.preventPullToRefresh();
        },

        preventPullToRefresh() {
            let lastTouchY = 0;
            let preventPullToRefresh = false;

            document.addEventListener(
                "touchstart",
                (e) => {
                    if (e.touches.length !== 1) return;
                    lastTouchY = e.touches[0].clientY;
                    preventPullToRefresh = window.pageYOffset === 0;
                },
                { passive: false }
            );

            document.addEventListener(
                "touchmove",
                (e) => {
                    const touchY = e.touches[0].clientY;
                    const touchYDelta = touchY - lastTouchY;
                    lastTouchY = touchY;

                    if (preventPullToRefresh && touchYDelta > 0) {
                        e.preventDefault();
                    }
                },
                { passive: false }
            );
        },

        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem("darkMode", this.darkMode);
            hapticFeedback("medium");
        },

        nextSlide() {
            hapticFeedback("light");
            if (this.onboardingSlide < 2) {
                this.onboardingSlide++;
            }
        },
    };
}

// ========================================
// OFFLINE/ONLINE DETECTION
// ========================================
window.addEventListener("load", function () {
    function updateOnlineStatus() {
        const status = navigator.onLine ? "online" : "offline";

        if (status === "offline") {
            showOfflineNotification();
        } else {
            hideOfflineNotification();
        }
    }

    function showOfflineNotification() {
        let offlineBanner = document.getElementById("offline-banner");
        if (!offlineBanner) {
            offlineBanner = document.createElement("div");
            offlineBanner.id = "offline-banner";
            offlineBanner.className = "offline-banner";
            offlineBanner.innerHTML = `
                <i class="bi bi-wifi-off"></i>
                <span>You are offline</span>
            `;
            document.body.prepend(offlineBanner);
        }
    }

    function hideOfflineNotification() {
        const offlineBanner = document.getElementById("offline-banner");
        if (offlineBanner) {
            offlineBanner.remove();
        }
    }

    window.addEventListener("online", updateOnlineStatus);
    window.addEventListener("offline", updateOnlineStatus);

    updateOnlineStatus();
});

// ========================================
// PWA INSTALL PROMPT
// ========================================
let deferredPrompt;

window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    deferredPrompt = e;

    // Only show install prompt if:
    // 1. Not already in standalone mode
    // 2. On mobile device
    // 3. User hasn't seen the required installation message
    const isMobile = window.innerWidth <= 430;
    const isAlreadyInstalled = window.matchMedia(
        "(display-mode: standalone)"
    ).matches;

    if (isMobile && !isAlreadyInstalled) {
        // Don't show banner - show the required installation page instead
        // The checkDevice function will handle this
        checkDevice();
    }
});

function showInstallPromotion() {
    // This function is kept for future use but won't be called
    // since we're requiring installation
    const dismissedTime = localStorage.getItem("install-prompt-dismissed");
    if (
        dismissedTime &&
        Date.now() - parseInt(dismissedTime) < 7 * 24 * 60 * 60 * 1000
    ) {
        return;
    }

    const installBanner = document.createElement("div");
    installBanner.className = "install-banner";
    installBanner.innerHTML = `
        <div class="install-content">
            <i class="bi bi-download"></i>
            <div>
                <strong>Install App</strong>
                <p>Install for a better experience</p>
            </div>
        </div>
        <button class="btn-install" onclick="installApp()">Install</button>
        <button class="btn-close-banner" onclick="dismissInstallPrompt(this)">×</button>
    `;

    document.body.appendChild(installBanner);
}

async function installApp() {
    if (!deferredPrompt) return;

    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;

    console.log(`User response: ${outcome}`);

    if (outcome === "accepted") {
        hapticFeedback("success");
        // Refresh to check installation status
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }

    deferredPrompt = null;
    document.querySelector(".install-banner")?.remove();
}

function dismissInstallPrompt(button) {
    localStorage.setItem("install-prompt-dismissed", Date.now().toString());
    button.closest(".install-banner").remove();
    hapticFeedback("light");
}

// ========================================
// PWA UPDATE NOTIFICATION
// ========================================
if ("serviceWorker" in navigator) {
    navigator.serviceWorker
        .register("/serviceworker.js")
        .then((registration) => {
            registration.addEventListener("updatefound", () => {
                const newWorker = registration.installing;

                newWorker.addEventListener("statechange", () => {
                    if (
                        newWorker.state === "installed" &&
                        navigator.serviceWorker.controller
                    ) {
                        showUpdateNotification();
                    }
                });
            });
        })
        .catch((err) => {
            console.log("Service Worker registration failed:", err);
        });
}

function showUpdateNotification() {
    const updateBanner = document.createElement("div");
    updateBanner.className = "install-banner";
    updateBanner.style.background = "#4CAF50";
    updateBanner.style.color = "white";
    updateBanner.innerHTML = `
        <div class="install-content">
            <i class="bi bi-arrow-repeat"></i>
            <div>
                <strong style="color: white;">Update Available</strong>
                <p style="color: rgba(255,255,255,0.9);">A new version is ready</p>
            </div>
        </div>
        <button class="btn-install" onclick="window.location.reload()" style="background: white; color: #4CAF50;">
            Reload
        </button>
        <button class="btn-close-banner" onclick="this.parentElement.remove()" style="color: white;">×</button>
    `;

    document.body.appendChild(updateBanner);
}

// Log PWA status on load
if (isPWA()) {
    console.log("Running as installed PWA");
} else {
    console.log("Running in browser - Install required");
}

// ========================================
// PREVENT ZOOM ON DOUBLE TAP (iOS)
// ========================================
let lastTouchEnd = 0;
document.addEventListener(
    "touchend",
    function (event) {
        const now = Date.now();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    },
    false
);

// ========================================
// SCREEN WAKE LOCK (Optional)
// ========================================
let wakeLock = null;

async function requestWakeLock() {
    try {
        if ("wakeLock" in navigator) {
            wakeLock = await navigator.wakeLock.request("screen");
            console.log("Wake Lock active");
        }
    } catch (err) {
        console.error(`Wake Lock error: ${err.name}, ${err.message}`);
    }
}

async function releaseWakeLock() {
    if (wakeLock !== null) {
        await wakeLock.release();
        wakeLock = null;
        console.log("Wake Lock released");
    }
}

// Optional: Request wake lock on certain pages
// requestWakeLock();
