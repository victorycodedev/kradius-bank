<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransferStatusNotification extends Notification
{
    use Queueable;


    public function __construct(public Transaction $transaction, public ?string $status = null) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        if ($this->transaction->transaction_type == 'transfer') {
            $subject = 'Transfer ' . ucfirst($this->status);
        } else if ($this->transaction->transaction_type == 'deposit') {
            $subject = 'Deposit ' . ucfirst($this->status);
        } else {
            $subject = 'Transaction ' . ucfirst($this->status);
        }

        $message = (new MailMessage)->subject($subject);

        if ($this->status === 'completed') {
            $message->success()
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line('Your transfer has been completed successfully.')
                ->line('**Transfer Details:**')
                ->line('Amount: ' . $this->transaction->userAccount->currency . number_format($this->transaction->amount, 2))
                ->line('Date: ' . $this->transaction->created_at->format('F j, Y g:i A'))
                // ->action('View Transaction', url('/transactions/' . $this->transaction->id))
                ->line('Thank you for using our service!');
        } elseif ($this->status === 'failed') {
            $message->error()
                ->greeting('Hello ' . $notifiable->name)
                ->line('Unfortunately, your transfer could not be completed.')
                ->line('**Transfer Details:**')
                ->line('Amount: ' . $this->transaction->userAccount->currency . number_format($this->transaction->amount, 2))
                // ->line('Reference: ' . $this->transaction->reference)
                ->when($this->transaction->failure_reason, function ($msg) {
                    return $msg->line('Reason: ' . $this->transaction->failure_reason);
                })
                // ->action('Try Again', url('/transfer'))
                ->line('Please contact support if you need assistance.');
        } else {
            $message->greeting('Hello ' . $notifiable->name)
                ->line('Your transfer is being processed.')
                ->line('**Transfer Details:**')
                ->line('Amount: ' . $this->transaction->userAccount->currency . number_format($this->transaction->amount, 2))
                // ->line('Reference: ' . $this->transaction->reference)
                ->line('We will notify you once the transfer is complete.');
        }

        return $message;
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
