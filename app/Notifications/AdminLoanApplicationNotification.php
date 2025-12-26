<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoanApplicationNotification extends Notification
{
    use Queueable;

    public $user;
    public $loan;

    public function __construct(User $user, Loan $loan)
    {
        $this->user = $user;
        $this->loan = $loan;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Loan Application - ' . $this->user->name)
            ->greeting('Hello Admin!')
            ->line('A user has submitted a loan application.')
            ->line('**Loan Details:**')
            ->line('User: ' . $this->user->name . ' (' . $this->user->email . ')')
            ->line('Amount: ' . '$' . number_format($this->loan->amount, 2))
            ->line('Duration: ' . $this->loan->duration_months . ' months')
            ->line('Interest Rate: ' . $this->loan->loanType->interest_rate . '%')
            ->line('Purpose: ' . ($this->loan->purpose ?? 'N/A'))
            ->line('Date: ' . $this->loan->created_at->format('F j, Y g:i A'))
            ->action('Review Application', url('/admin/loans/' . $this->loan->id))
            ->line('Please review the loan application.');
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
