<?php

namespace App\Livewire\MobileApp\Screen\More;

use App\Models\Settings;
use Livewire\Attributes\Title;
use Livewire\Component;

class About extends Component
{
    #[Title('About')]
    public function render()
    {
        $settings = Settings::get();

        return view('livewire.mobile-app.screen.more.about', [
            'appName' => config('app.name'),
            'appVersion' => config('app.version', '1.0.0'),
            'companyName' => $settings->company_name ?? config('app.name'),
            'supportEmail' => $settings->support_email ?? config('mail.from.address'),
            'companyAddress' => $settings->company_address ?? '',
            'companyPhone' => $settings->company_phone ?? '',
        ]);
    }
}
