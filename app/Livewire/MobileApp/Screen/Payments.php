<?php

namespace App\Livewire\MobileApp\Screen;

use Livewire\Attributes\Title;
use Livewire\Component;

class Payments extends Component
{
    #[Title('Payments')]
    public function render()
    {
        return view('livewire.mobile-app.screen.payments');
    }
}
