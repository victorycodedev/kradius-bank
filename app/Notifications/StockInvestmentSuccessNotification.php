<?php

namespace App\Notifications;

use App\Models\Investment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockInvestmentSuccessNotification extends Notification
{
    use Queueable;

    public function __construct(public Investment $investment) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->success()
            ->subject('Investment Successful - ' . $this->investment->symbol)
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('Your stock investment has been successfully processed.')
            ->line('**Investment Details:**')
            ->line('Stock: ' . $this->investment->stock->name . ' (' . $this->investment->stock->symbol . ')')
            ->line('Shares Purchased: ' . $this->investment->shares)
            ->line('Price per Share: ' . '$' . number_format($this->investment->purchase_price, 2))
            ->line('Total Investment: ' . '$' . number_format($this->investment->amount, 2))
            ->line('Date: ' . $this->investment->created_at->format('F j, Y g:i A'))
            // ->action('View Portfolio', url('/investments'))
            ->line('Thank you for investing with us!');
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
