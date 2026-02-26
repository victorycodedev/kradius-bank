<?php

namespace App\Livewire\MobileApp\Screen;

use App\Models\AdNotifcation;
use App\Models\InvestmentSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

class Home extends Component
{
    public User $user;
    public Collection $accounts;
    public array $notifications = [];
    public ?string $avatarUrl = null;
    public $enable_stocks = false;

    public function mount(): void
    {
        $stockSettings = InvestmentSetting::find(1);
        $this->enable_stocks = $stockSettings->investments_enabled;

        $this->user = Auth::user();

        if ($this->user->account_status == 'suspended' || $this->user->account_status == 'closed') {
            // logged out user
            Auth::logout();
            redirect()->route('login');
            return;
        }

        if ($this->user->hasMedia('avatars')) {
            $this->avatarUrl = $this->user->getFirstMediaUrl('avatars');
        }

        $this->accounts = $this->user->accounts()->orderByDesc('is_primary')->get();

        $this->notifications = AdNotifcation::where('is_active', true)->orderBy('created_at')->get()->toArray();
    }

    #[Title('Home')]
    public function render()
    {
        $recentTransactions = $this->user->accounts()
            ->with(['transactions' => function ($query) {
                $query->where(function ($q) {
                    $q->where('status', 'completed')
                        ->orWhere('status', 'pending_verification');
                })
                    ->latest()
                    ->limit(10);
            }])
            ->get()
            ->pluck('transactions')
            ->flatten()
            ->sortByDesc('created_at')
            ->take(3);

        return view('livewire.mobile-app.screen.home', [
            'notifications' => $this->notifications,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
