<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminDepositNotification extends Notification
{
    use Queueable;

    public $user;
    public $deposit;

    public function __construct(User $user, $deposit)
    {
        $this->user = $user;
        $this->deposit = $deposit;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Deposit Request - ' . $this->user->name)
            ->greeting('Hello Admin!')
            ->line('A user has submitted a deposit request.')
            ->line('**Deposit Details:**')
            ->line('User: ' . $this->user->name . ' (' . $this->user->email . ')')
            ->line('Amount: ' . $this->deposit->userAccount->currency . number_format($this->deposit->amount, 2))
            // ->line('Reference: ' . $this->deposit->reference)
            ->line('Payment Method: ' . ($this->deposit->metadata['payment_method'] ?? 'N/A'))
            ->line('Date: ' . $this->deposit->created_at->format('F j, Y g:i A'))
            ->action('Review Deposit', url('/admin/transactions/' . $this->deposit->id))
            ->line('Please review and approve the deposit.');
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
