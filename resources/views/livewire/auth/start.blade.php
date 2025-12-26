 <div class="screen login-screen">
     <div class="auth-header mb-5">
         <h1>Forgot password</h1>
         <p>Enter your email to receive a password reset link</p>
     </div>

     <x-auth-session-status class="text-center" :status="session('status')" />

     <form method="POST" action="{{ route('password.email') }}" class="auth-form">
         @csrf
         <div class="form-group mb-3">
             <x-form.input name="email" label="Email" required autofocus autocomplete="email"
                 placeholder="email@example.com" />
         </div>


         <button type="submit" class="btn btn-primary btn-lg w-100">Email password reset link</button>

         <div class="auth-footer">
             <p>
                 Remember your password?
                 <a href="{{ route('login') }}" class="link">Sign in</a>
             </p>
         </div>
     </form>
 </div>
