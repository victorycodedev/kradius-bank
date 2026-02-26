<?php

namespace App\Livewire\MobileApp\Screen;

use App\Models\Investment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

class InvestmentDetails extends Component
{
    public Investment $investment;
    public $activeTab = 'overview'; // overview, transactions, profits

    public function mount($id)
    {
        $this->investment = Investment::with(['stock', 'userAccount', 'transactions', 'profits'])
            ->where('user_id', Auth::user()->id)
            ->findOrFail($id);

        $this->authorize('view', $this->investment);
    }


    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function refreshData()
    {
        $this->investment->calculateCurrentValue();
        $this->investment->refresh();
        session()->flash('success', 'Investment data refreshed!');
    }

    #[Title('Investment Details')]
    public function render()
    {
        return view('livewire.mobile-app.screen.investment-details');
    }
}
