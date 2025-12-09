<?php

namespace App\Livewire\MobileApp\Screen;

use Livewire\Attributes\Title;
use Livewire\Component;

class More extends Component
{
    #[Title('More')]
    public function render()
    {
        return view('livewire.mobile-app.screen.more');
    }
}
