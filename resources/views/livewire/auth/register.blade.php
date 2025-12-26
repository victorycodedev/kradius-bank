<x-layouts.auth>
    <div class="screen register-screen">
        <div class="auth-header">
            {{-- <a href="{{ route('login') }}" class="back-btn">
                <i class="bi bi-arrow-left"></i>
            </a> --}}
            <h1>Create Account</h1>
            <p>Sign up to get started</p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="auth-form">
            @csrf
            <div class="form-group">
                <x-form.input label="Firstname" name="first_name" required autofocus placeholder="Enter your first name" />
            </div>

            <div class="form-group">
                <x-form.input label="Lastname" name="last_name" required placeholder="Enter your last name" />
            </div>

            <div class="form-group">
                <x-form.input name="email" label="Email" required autofocus autocomplete="email"
                    placeholder="eg email@example.com" />
            </div>

            <div class="form-group">
                <x-form.input type="tel" name="phone_number" label="Phone Number" required
                    placeholder="eg +1 123 456 789" />
            </div>

            <div class="form-group">
                <label class="form-label fw-semibold">Password</label>
                <div class="password-input">
                    <input :type="showPassword ? 'text' : 'password'" class="form-control" name="password"
                        placeholder="Create a password" required>
                    <button type="button" @click="showPassword = !showPassword" class="password-toggle">
                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                </div>
                @error('password')
                    <div class="text-danger small mt-2">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label fw-semibold">Confirm Password</label>
                <div class="password-input">
                    <input :type="showConfirmPassword ? 'text' : 'password'" class="form-control"
                        name="password_confirmation" placeholder="Confirm your password" required>
                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="password-toggle">
                        <i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                </div>
            </div>

            <label class="checkbox-label mb-3">
                <input type="checkbox" required>
                <span>I agree to the <a href="#" class="link">Terms & Conditions</a></span>
            </label>

            <button type="submit" class="btn btn-primary btn-lg w-100">Create Account</button>

            <div class="auth-footer">
                <p>Already have an account? <a href="{{ route('login') }}" class="link">Login</a></p>
            </div>
        </form>
    </div>
</x-layouts.auth>
