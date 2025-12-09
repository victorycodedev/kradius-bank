<?php

namespace App\Livewire\MobileApp\Screen;

use Livewire\Attributes\Title;
use Livewire\Component;

class Loan extends Component
{
    #[Title('Loans')]
    public function render()
    {
        return view('livewire.mobile-app.screen.loan');
    }
}
