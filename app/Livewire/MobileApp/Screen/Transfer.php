<?php

namespace App\Livewire\MobileApp\Screen;

use Livewire\Attributes\Title;
use Livewire\Component;

class Transfer extends Component
{
    #[Title('Transfers')]

    public function render()
    {
        return view('livewire.mobile-app.screen.transfer');
    }
}
