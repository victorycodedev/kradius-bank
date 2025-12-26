<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserKycStatusUpdate extends Notification
{
    use Queueable;

    public $status; // approved, rejected, pending
    public $reason;

    public function __construct($status, $reason = null)
    {
        $this->status = $status;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $subject = 'KYC Verification ' . ucfirst($this->status);
        $message = (new MailMessage)->subject($subject);

        if ($this->status === 'verified') {
            $message->greeting('Congratulations ' . $notifiable->name . '!')
                ->line('Your KYC verification has been approved!')
                ->line('You now have full access to all platform features.')
                ->action('Access Your Account', url('/dashboard'))
                ->line('Thank you for completing the verification process.');
        } elseif ($this->status === 'rejected') {
            $message->greeting('Hello ' . $notifiable->name)
                ->line('Unfortunately, your KYC verification was not approved.')
                ->when($this->reason, function ($msg) {
                    return $msg->line('**Reason:** ' . $this->reason);
                })
                ->line('Please review the requirements and resubmit your application.')
                ->action('Resubmit KYC', url('/kyc/submit'))
                ->line('If you have any questions, please contact our support team.');
        } else {
            $message->greeting('Hello ' . $notifiable->name)
                ->line('Your KYC application is currently under review.')
                ->line('We will notify you once the verification is complete.')
                ->line('This process typically takes 24-48 hours.');
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
