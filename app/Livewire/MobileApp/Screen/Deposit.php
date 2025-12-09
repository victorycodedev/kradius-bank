<?php

namespace App\Livewire\MobileApp\Screen;

use Livewire\Attributes\Title;
use Livewire\Component;

class Deposit extends Component
{
    #[Title('Deposit')]
    public function render()
    {
        return view('livewire.mobile-app.screen.deposit');
    }
}
