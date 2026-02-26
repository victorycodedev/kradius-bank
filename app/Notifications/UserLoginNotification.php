<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserLoginNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $loginTime;
    public $ipAddress;
    public $userAgent;
    public $location;

    public function __construct($loginTime, $ipAddress, $userAgent, $location = null)
    {
        $this->loginTime = $loginTime;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->location = $location;
    }

    public function via(object $notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Login to Your Account')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We detected a new login to your account.')
            ->line('**Login Details:**')
            ->line('Time: ' . $this->loginTime->format('F j, Y g:i A'))
            ->line('IP Address: ' . $this->ipAddress)
            ->line('Device: ' . $this->userAgent)
            ->when($this->location, function ($message) {
                return $message->line('Location: ' . $this->location);
            })
            ->line('If this was you, you can safely ignore this email.')
            ->action('Secure Your Account', url('/account/security'))
            ->line('If you did not perform this login, please secure your account immediately.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
