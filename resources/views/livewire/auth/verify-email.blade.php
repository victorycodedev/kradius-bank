<x-layouts.auth>

    <div class="screen login-screen">
        <div class="auth-header mb-5">
            <h1>Email Verification</h1>
            <p>Please verify your email address by clicking on the link we just emailed to you</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
            <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </flux:text>
        @endif
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-lg w-100">
                {{ __('Resend verification email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <div class="text-center mt-5">
                <button type="submit" class="btn btn-link">
                    {{ __('Log out') }}
                </button>
            </div>
        </form>
    </div>

</x-layouts.auth>
