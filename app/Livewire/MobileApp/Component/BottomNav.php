<?php

namespace App\Livewire\MobileApp\Component;

use App\Models\LoanSetting;
use Livewire\Component;

class BottomNav extends Component
{
    public $loan_feature_enabled = false;

    public function mount(): void
    {
        $this->loan_feature_enabled = LoanSetting::find(1)->loan_applications_enabled;
    }

    public function render()
    {
        return view('livewire.mobile-app.component.bottom-nav');
    }
}
