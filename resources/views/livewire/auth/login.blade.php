<x-layouts.auth>
    <!-- Login Screen -->
    <div class="screen login-screen">
        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Login to your account</p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="auth-form">
            @csrf
            <div class="form-group mb-3">
                <x-form.input name="email" label="Email" required autofocus autocomplete="email"
                    placeholder="email@example.com" />
            </div>

            <div class="form-group">
                <label class="form-label fw-semibold">Password</label>
                <div class="password-input">
                    <input :type="showPassword ? 'text' : 'password'" class="form-control" name="password"
                        placeholder="Enter your password" required autocomplete="current-password">
                    <button type="button" @click="showPassword = !showPassword" class="password-toggle">
                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" @checked(old('remember'))>
                    <span>Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="link">Forgot Password?</a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Login</button>

            {{-- <div class="divider">
                <span>OR</span>
            </div>

            <button type="button" class="btn btn-outline-secondary btn-lg w-100">
                <i class="bi bi-google"></i> Continue with Google
            </button> --}}

            @if ($configuration->allow_registration)
                <div class="auth-footer">
                    <p>
                        Don't have an account?
                        <a href="{{ route('register') }}" class="link">Sign Up</a>
                    </p>
                </div>
            @endif
        </form>
    </div>
</x-layouts.auth>
