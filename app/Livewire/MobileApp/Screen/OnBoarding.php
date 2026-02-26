<?php

namespace App\Livewire\MobileApp\Screen;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class OnBoarding extends Component
{
    #[Title('Welcome')]

    public function mount()
    {
        if (Auth::check()) {
            return $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function goToLogin()
    {
        return $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.mobile-app.screen.on-boarding');
    }
}
