<div class="screen onboarding-screen">
    <div class="onboarding-content">
        <div class="onboarding-slides">
            <div x-show="onboardingSlide === 0" class="slide">
                <div class="icon-circle">
                    <i class="bi bi-wallet2"></i>
                </div>
                <h2>Manage Your Money</h2>
                <p>Track your expenses and income with ease. Take control of your financial future.</p>
            </div>

            <div x-show="onboardingSlide === 1" class="slide">
                <div class="icon-circle">
                    <i class="bi bi-send"></i>
                </div>
                <h2>Fast Transfers</h2>
                <p>Send money instantly to anyone, anywhere. Safe and secure transactions.</p>
            </div>

            <div x-show="onboardingSlide === 2" class="slide">
                <div class="icon-circle">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h2>Secure & Safe</h2>
                <p>Your security is our priority. Bank-level encryption protects your data.</p>
            </div>
        </div>

        <div class="dots">
            <span :class="{ 'active': onboardingSlide === 0 }"></span>
            <span :class="{ 'active': onboardingSlide === 1 }"></span>
            <span :class="{ 'active': onboardingSlide === 2 }"></span>
        </div>

        <div class="onboarding-actions">
            <button @click="nextSlide()" class="btn btn-primary btn-lg w-100"
                x-text="onboardingSlide === 2 ? 'Get Started' : 'Next'"></button>
            <button @click="currentScreen = 'login'" class="btn btn-link">Skip</button>
        </div>
    </div>
</div>
