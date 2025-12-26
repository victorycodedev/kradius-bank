<?php

namespace App\Listeners;

use App\Models\Settings;
use App\Notifications\UserLoginNotification;
use Illuminate\Auth\Events\Login as EventsLogin;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;

class SendLoginSuccessNotification
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
    public function handle(EventsLogin $event): void
    {
        try {
            $settings = Settings::get();
            $request = request();
            if ($settings->notify_on_login) {
                $user = $event->user;
                $user->notify(new UserLoginNotification(
                    now(),
                    $request->ip(),
                    $request->userAgent(),
                    null // You can add location detection if needed
                ));
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
