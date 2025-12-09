function balanceSliderComponent(totalSlides) {
    return {
        currentSlide: 0,
        total: totalSlides,
        touchStartX: 0,
        touchEndX: 0,

        // Touch start
        start(e) {
            if (this.total <= 1) return; // No swipe if only 1 card
            this.touchStartX = e.touches[0].clientX;
        },

        // Touch move
        move(e) {
            if (this.total <= 1) return;
            this.touchEndX = e.touches[0].clientX;
        },

        // Touch end
        end() {
            if (this.total <= 1) return;

            const diff = this.touchStartX - this.touchEndX;
            const threshold = 50;

            if (diff > threshold && this.currentSlide < this.total - 1) {
                this.currentSlide++;
            } else if (diff < -threshold && this.currentSlide > 0) {
                this.currentSlide--;
            }

            this.touchStartX = 0;
            this.touchEndX = 0;
        },
    };
}

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

        /* Auto slide every few seconds */
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

        /* Go to next slide */
        next() {
            if (this.current < this.total - 1) {
                this.current++;
            } else {
                this.current = 0; // loop back
            }
        },

        /* Touch START */
        start(e) {
            if (this.total <= 1) return;
            this.touchStartX = e.touches[0].clientX;
            this.stopAutoSlide(); // pause during manual swipe
        },

        /* Touch MOVE */
        move(e) {
            if (this.total <= 1) return;
            this.touchEndX = e.touches[0].clientX;
        },

        /* Touch END */
        end() {
            if (this.total <= 1) return;

            const diff = this.touchStartX - this.touchEndX;
            const threshold = 50;

            if (diff > threshold) {
                // swipe left (next)
                this.next();
            } else if (diff < -threshold && this.current > 0) {
                // swipe right (previous)
                this.current--;
            }

            this.touchStartX = 0;
            this.touchEndX = 0;

            // resume auto slide
            this.resetTimer();
        },
    };
}

document.addEventListener("livewire:init", () => {
    Livewire.hook("commit", ({ component, commit, respond, succeed, fail }) => {
        // Any custom code that needs to run after component updates
        // PHPFlasher will automatically handle the notifications
    });
});

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
        },

        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem("darkMode", this.darkMode);
        },

        nextSlide() {
            if (this.onboardingSlide < 2) {
                this.onboardingSlide++;
            } else {
                this.currentScreen = "login";
            }
        },
    };
}
