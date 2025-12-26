<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanStatusNotification extends Notification
{
    use Queueable;

    public $loan;
    public $status; // approved, rejected, pending, disbursed

    public function __construct(Loan $loan, $status)
    {
        $this->loan = $loan;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $subject = 'Loan Application ' . ucfirst($this->status);
        $message = (new MailMessage)->subject($subject);

        if ($this->status === 'active') {
            $message->success()
                ->greeting('Congratulations ' . $notifiable->name . '!')
                ->line('Your loan application has been approved!')
                ->line('**Loan Details:**')
                ->line('Amount: ' . '$' . number_format($this->loan->amount, 2))
                ->line('Duration: ' . $this->loan->duration_months . ' months')
                ->line('Interest Rate: ' . $this->loan->loanType->interest_rate . '%')
                ->line('Monthly Payment: ' . '$' . number_format($this->loan->monthly_payment, 2))
                ->line('The funds will be disbursed to your account shortly.')
                // ->action('View Loan Details', url('/loans/' . $this->loan->id))
                ->line('Thank you for choosing our service!');
        } elseif ($this->status === 'disbursed') {
            $message->success()
                ->greeting('Great News ' . $notifiable->name . '!')
                ->line('Your loan has been disbursed to your account.')
                ->line('**Loan Details:**')
                ->line('Amount: ' . '$' . number_format($this->loan->amount, 2))
                ->line('Payment Due Date: ' . $this->loan->due_date->format('F j, Y'))
                // ->action('View Loan Details', url('/loans/' . $this->loan->id))
                ->line('Please ensure timely repayment to maintain a good credit record.');
        } elseif ($this->status === 'rejected') {
            $message->error()
                ->greeting('Hello ' . $notifiable->name)
                ->line('Unfortunately, your loan application was not approved.')
                ->when($this->loan->rejection_reason, function ($msg) {
                    return $msg->line('**Reason:** ' . $this->loan->rejection_reason);
                })
                ->line('You may reapply after addressing the concerns.')
                ->action('Contact Support', url('/account/faqs-and-support'))
                ->line('Please contact support if you have any questions.');
        } else {
            $message->greeting('Hello ' . $notifiable->name)
                ->line('Your loan application is under review.')
                ->line('**Loan Details:**')
                ->line('Amount: ' . '$' . number_format($this->loan->amount, 2))
                ->line('Duration: ' . $this->loan->duration_months . ' months')
                ->line('We will notify you once the review is complete.')
                ->line('This process typically takes 2-3 business days.');
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
