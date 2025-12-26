<?php

namespace App\Listeners;

use App\Events\Registered;
use App\Models\Settings;
use App\Notifications\AdminNewUserRegistration;
use Illuminate\Auth\Events\Registered as EventsRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendRegisterSuccessNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventsRegistered $event): void
    {
        $settings = Settings::get();

        if ($settings->email_notifications_enabled) {
            Notification::route('mail', $settings->notifiable_email)
                ->notify(new AdminNewUserRegistration($event->user));
        }
    }
}
