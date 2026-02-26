<?php

use EragLaravelPwa\EragLaravelPwaServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\FortifyServiceProvider::class,
    EragLaravelPwaServiceProvider::class,
];
