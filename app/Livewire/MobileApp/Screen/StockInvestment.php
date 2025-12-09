<?php

namespace App\Livewire\MobileApp\Screen;

use Livewire\Attributes\Title;
use Livewire\Component;

class StockInvestment extends Component
{
    #[Title('Stock investment')]
    public function render()
    {
        return view('livewire.mobile-app.screen.stock-investment');
    }
}
