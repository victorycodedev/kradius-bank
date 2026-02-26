<x-layouts.auth>
    <div class="screen login-screen" x-cloak x-data="{
        showRecoveryInput: @js($errors->has('recovery_code')),
        code: '',
        recovery_code: '',
        toggleInput() {
            this.showRecoveryInput = !this.showRecoveryInput;
    
            this.code = '';
            this.recovery_code = '';
    
            $dispatch('clear-2fa-auth-code');
    
            $nextTick(() => {
                this.showRecoveryInput ?
                    this.$refs.recovery_code?.focus() :
                    $dispatch('focus-2fa-auth-code');
            });
        },
    }">
        <div class="auth-header mb-5" x-show="!showRecoveryInput">
            <h1>Authentication Code</h1>
            <p>Enter the authentication code provided by your authenticator application.</p>
        </div>

        <div class="auth-header mb-5" x-show="showRecoveryInput">
            <h1>Recovery Code</h1>
            <p>Please confirm access to your account by entering one of your emergency recovery codes.</p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('two-factor.login.store') }}" class="auth-form">
            @csrf
            <div class="form-group mb-3" x-show="!showRecoveryInput">
                <x-input-otp name="code" digits="6" autocomplete="one-time-code" x-model="code" />
                @error('code')
                    <span class="text-danger">
                        {{ $message }}
                    </span>
                @enderror
            </div>
            <div class="form-group mb-3" x-show="showRecoveryInput">
                <x-form.input type="text" name="recovery_code" labe="Recovery Code" x-ref="recovery_code"
                    x-bind:required="showRecoveryInput" autocomplete="one-time-code" x-model="recovery_code" />
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Continue</button>

            <div class="mt-5 text-sm text-center">
                <span class="opacity-50">{{ __('or you can') }}</span>
                <div class="inline font-medium underline cursor-pointer opacity-80">
                    <span x-show="!showRecoveryInput"
                        @click="toggleInput()">{{ __('login using a recovery code') }}</span>
                    <span x-show="showRecoveryInput"
                        @click="toggleInput()">{{ __('login using an authentication code') }}</span>
                </div>
            </div>
        </form>
    </div>
</x-layouts.auth>
