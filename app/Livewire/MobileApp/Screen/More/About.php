<?php

namespace App\Livewire\MobileApp\Screen\More;

use App\Models\Settings;
use Livewire\Attributes\Title;
use Livewire\Component;

class About extends Component
{
    public bool $showTernsModal = false;
    public bool $showPrivacyModal = false;
    #[Title('About')]
    public function render()
    {
        $settings = Settings::get();

        return view('livewire.mobile-app.screen.more.about');
    }
}
