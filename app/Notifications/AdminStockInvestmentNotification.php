<?php

namespace App\Notifications;

use App\Models\Investment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminStockInvestmentNotification extends Notification
{
    use Queueable;
    public $user;
    public $investment;

    public function __construct(User $user, Investment $investment)
    {
        $this->user = $user;
        $this->investment = $investment;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Stock Investment - ' . $this->user->name)
            ->greeting('Hello Admin!')
            ->line('A user has made a new stock investment.')
            ->line('**Investment Details:**')
            ->line('User: ' . $this->user->name . ' (' . $this->user->email . ')')
            ->line('Stock: ' . $this->investment->stock->name . ' (' . $this->investment->stock->symbol . ')')
            ->line('Amount: ' . '$' . number_format($this->investment->amount, 2))
            ->line('Shares: ' . $this->investment->shares)
            ->line('Price per Share: ' . '$' . number_format($this->investment->price_per_share, 2))
            ->line('Date: ' . $this->investment->created_at->format('F j, Y g:i A'))
            ->action('View Investment', url('/admin/investments/' . $this->investment->id))
            ->line('Investment recorded successfully.');
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
