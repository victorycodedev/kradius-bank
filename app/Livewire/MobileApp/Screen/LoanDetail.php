<?php

namespace App\Livewire\MobileApp\Screen;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Traits\HasAlerts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

class LoanDetail extends Component
{
    use HasAlerts;

    public Loan $loan;
    public $showPaymentModal = false;
    public $selectedRepayment = null;
    public $selectedAccount = '';
    public $paymentNotes = '';

    public function mount($id)
    {
        $this->loan = Loan::with(['loanType', 'repayments' => function ($query) {
            $query->orderBy('due_date', 'asc');
        }])->where('user_id', Auth::id())->findOrFail($id);
    }

    #[Title('Loan Details')]
    public function render()
    {
        return view('livewire.mobile-app.screen.loan-detail', [
            'repayments' => $this->loan->repayments,
            'paidCount' => $this->loan->repayments()->where('status', 'paid')->count(),
            'pendingCount' => $this->loan->repayments()->where('status', 'pending')->count(),
            'overdueCount' => $this->loan->repayments()->where('status', 'overdue')->count(),
        ]);
    }

    public function openPayFullModal(): void
    {
        $this->showPaymentModal = true;
        $this->selectedRepayment = null;
    }

    public function selectRepayment($repaymentId)
    {
        $this->selectedRepayment = LoanRepayment::where('loan_id', $this->loan->id)
            ->findOrFail($repaymentId);

        if ($this->selectedRepayment->isPaid()) {
            $this->errorAlert(message: 'This repayment has already been paid.');
            return;
        }

        $this->showPaymentModal = true;

        $this->resetValidation();
    }

    public function closeModal()
    {
        // $this->showPaymentModal = false;
        $this->dispatch('close-bottom-sheet', id: 'showPaymentModal');
        $this->selectedRepayment = null;
        $this->selectedAccount = '';
        $this->paymentNotes = '';
        $this->resetValidation();
    }

    public function makePayment()
    {
        $this->validate([
            'selectedAccount' => 'required|exists:user_accounts,id',
        ]);

        $account = Auth::user()->accounts()
            ->where('id', $this->selectedAccount)
            ->firstOrFail();

        // Check if account has sufficient balance
        if ($account->balance < $this->selectedRepayment->amount) {
            $this->errorAlert(message: 'Insufficient balance in selected account.');
            return;
        }

        DB::beginTransaction();
        try {
            // Create transaction
            $transaction = $account->transactions()->create([
                'transaction_type' => 'debit',
                'amount' => $this->selectedRepayment->amount,
                'currency' => $account->currency,
                'balance_before' => $account->balance,
                'balance_after' => $account->balance - $this->selectedRepayment->amount,
                'reference_number' => 'LRP' . strtoupper(uniqid()),
                'description' => "Loan repayment for {$this->loan->reference_number} - {$this->selectedRepayment->reference_number}",
                'status' => 'completed',
                'channel' => 'mobile_app',
                'completed_at' => now(),
            ]);

            // Deduct from account
            $account->decrement('balance', $this->selectedRepayment->amount);

            // Update repayment
            $this->selectedRepayment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'bank_transfer',
                'transaction_id' => $transaction->id,
                'notes' => $this->paymentNotes,
            ]);

            // Update loan outstanding balance
            $this->loan->outstanding_balance -= $this->selectedRepayment->amount;

            // Check if loan is fully paid
            if ($this->loan->outstanding_balance <= 0) {
                $this->loan->status = 'completed';
                $this->loan->outstanding_balance = 0;
            } elseif ($this->loan->status === 'approved') {
                $this->loan->status = 'active';
            }

            $this->loan->save();

            // Log activity
            $this->loan->logActivity(
                'payment_received',
                'Payment of ' . $account->currency . ' ' . number_format($this->selectedRepayment->amount, 2) . ' received for repayment ' . $this->selectedRepayment->reference_number,
                Auth::id()
            );

            DB::commit();
            $this->successAlert(message: 'Payment successful!');
            $this->closeModal();
            $this->loan->refresh();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorAlert(message: 'Payment failed. Please try again.');
        }
    }

    public function payInFull()
    {
        $this->validate([
            'selectedAccount' => 'required|exists:user_accounts,id',
        ]);

        $account = Auth::user()->accounts()
            ->where('id', $this->selectedAccount)
            ->firstOrFail();

        $totalOutstanding = $this->loan->outstanding_balance;

        // Check if account has sufficient balance
        if ($account->balance < $totalOutstanding) {
            $this->errorAlert(message: 'Insufficient balance in selected account.');
            return;
        }

        DB::beginTransaction();
        try {
            // Create transaction
            $transaction = $account->transactions()->create([
                'transaction_type' => 'debit',
                'amount' => $totalOutstanding,
                'currency' => $account->currency,
                'balance_before' => $account->balance,
                'balance_after' => $account->balance - $totalOutstanding,
                'reference_number' => 'LRP' . strtoupper(uniqid()),
                'description' => "Full loan repayment for {$this->loan->reference_number}",
                'status' => 'completed',
                'channel' => 'mobile_app',
                'completed_at' => now(),
            ]);

            // Deduct from account
            $account->decrement('balance', $totalOutstanding);

            // Mark all pending repayments as paid
            $pendingRepayments = $this->loan->repayments()
                ->whereIn('status', ['pending', 'overdue'])
                ->get();

            foreach ($pendingRepayments as $repayment) {
                $repayment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => 'bank_transfer',
                    'transaction_id' => $transaction->id,
                    'notes' => 'Paid as part of full loan settlement',
                ]);
            }

            // Update loan
            $this->loan->update([
                'status' => 'completed',
                'outstanding_balance' => 0,
            ]);

            // Log activity
            $this->loan->logActivity(
                'loan_completed',
                'Loan fully paid. Total amount: ' . $account->currency . ' ' . number_format($totalOutstanding, 2),
                Auth::id()
            );

            DB::commit();

            $this->successAlert(message: 'Loan fully paid! Congratulations!');
            $this->closeModal();
            $this->loan->refresh();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorAlert(message: 'Payment failed. Please try again.');
            // \Log::error('Full loan repayment error: ' . $e->getMessage());
        }
    }
}