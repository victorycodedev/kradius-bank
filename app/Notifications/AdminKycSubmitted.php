<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminKycSubmitted extends Notification
{
    use Queueable;
    public $user;
    public $kycData;

    public function __construct(User $user, $kycData = null)
    {
        $this->user = $user;
        $this->kycData = $kycData;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New KYC Application - ' . $this->user->name)
            ->greeting('Hello Admin!')
            ->line('A user has submitted a KYC application for verification.')
            ->line('**User Details:**')
            ->line('Name: ' . $this->user->name)
            ->line('Email: ' . $this->user->email)
            ->line('Submitted: ' . now()->format('F j, Y g:i A'))
            ->action('Review KYC Application', url('/admin/users/' . $this->user->id))
            ->line('Please review and verify the submitted documents.');
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
