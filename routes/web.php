<?php

use App\Livewire\MobileApp\Screen\Deposit;
use App\Livewire\MobileApp\Screen\Home;
use App\Livewire\MobileApp\Screen\InvestmentDetails;
use App\Livewire\MobileApp\Screen\Loan;
use App\Livewire\MobileApp\Screen\More;
use App\Livewire\MobileApp\Screen\Payments;
use App\Livewire\MobileApp\Screen\StockInvestment;
use App\Livewire\MobileApp\Screen\Transfer;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', Home::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware([
    'auth',
    'verified',
])
    ->group(function () {
        Route::get('dashboard', Home::class)->name('dashboard');
        Route::get('transfer', Transfer::class)->name('transfer');
        Route::get('loans', Loan::class)->name('loans');
        Route::get('payments', Payments::class)->name('payments');
        Route::get('more', More::class)->name('more');
        Route::get('deposit', Deposit::class)->name('deposit');
        Route::get('stock-investment', StockInvestment::class)->name('stock');
        Route::get('investment/{id}', InvestmentDetails::class)
            ->name('investment-detail');
    });

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
