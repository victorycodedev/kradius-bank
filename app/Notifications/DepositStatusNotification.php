<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositStatusNotification extends Notification
{
    use Queueable;

    public $deposit;
    public $status; // approved, rejected, pending

    public function __construct($deposit, $status)
    {
        $this->deposit = $deposit;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $subject = 'Deposit ' . ucfirst($this->status);
        $message = (new MailMessage)->subject($subject);

        if ($this->status === 'completed') {
            $message->success()
                ->greeting('Great News ' . $notifiable->name . '!')
                ->line('Your deposit has been approved and credited to your account.')
                ->line('**Deposit Details:**')
                ->line('Amount: ' . $this->deposit->userAccount->currency . number_format($this->deposit->amount, 2))
                // ->line('Reference: ' . $this->deposit->reference)
                ->line('Date: ' . $this->deposit->created_at->format('F j, Y g:i A'))
                // ->action('View Balance', url('/dashboard'))
                ->line('Thank you for your deposit!');
        } elseif ($this->status !== 'completed') {
            $message->error()
                ->greeting('Hello ' . $notifiable->name)
                ->line('Your deposit request could not be completed.')
                ->line('**Deposit Details:**')
                ->line('Amount: ' . $this->deposit->userAccount->currency . number_format($this->deposit->amount, 2))
                // ->line('Reference: ' . $this->deposit->reference)
                // ->when($this->deposit->rejection_reason, function ($msg) {
                //     return $msg->line('Reason: ' . $this->deposit->rejection_reason);
                // })
                ->action('Contact Support', url('/account/faqs-and-support'))
                ->line('Please contact support for more information.');
        } else {
            $message->greeting('Hello ' . $notifiable->name)
                ->line('Your deposit is being processed.')
                ->line('**Deposit Details:**')
                ->line('Amount: ' . $this->deposit->userAccount->currency . number_format($this->deposit->amount, 2))
                // ->line('Reference: ' . $this->deposit->reference)
                ->line('We will notify you once your deposit is approved.');
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
