<?php

namespace App\Livewire\MobileApp\Screen;

use App\Models\Loan as ModelsLoan;
use App\Models\LoanType;
use App\Traits\HasAlerts;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

class Loan extends Component
{
    use HasAlerts;

    public $activeTab = 'available'; // available, my-loans
    public $showApplyModal = false;
    public $selectedLoanType = null;

    // Application form fields
    public $loanTypeId = '';
    public $amount = '';
    public $durationMonths = '';
    public $purpose = '';
    public $employmentStatus = '';
    public $monthlyIncome = '';
    public $additionalInfo = '';
    public $monthsList = [];

    // Calculated fields
    public $calculatedInterest = 0;
    public $calculatedMonthlyPayment = 0;
    public $calculatedTotalPayable = 0;

    public function mount(): void
    {
        $this->monthsList = range(1, 12);
    }

    #[Title('Loans')]
    public function render()
    {
        $loanTypes = LoanType::where('is_active', true)
            ->orderBy('name')
            ->get();

        $myLoans = ModelsLoan::where('user_id', Auth::id())
            ->with('loanType')
            ->latest()
            ->get();

        return view('livewire.mobile-app.screen.loan', [
            'loanTypes' => $loanTypes,
            'myLoans' => $myLoans,
        ]);
    }

    public function selectLoanType($loanTypeId)
    {
        $this->selectedLoanType = LoanType::find($loanTypeId);
        $this->loanTypeId = $loanTypeId;
        // $this->showApplyModal = true;
        // $this->dispatch('open-bottom-sheet', id: 'showApplyModal');
        $this->resetForm();
    }

    public function closeModal()
    {
        // $this->showApplyModal = false;
        $this->dispatch('close-bottom-sheet', id: 'showApplyModal');
        $this->selectedLoanType = null;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm()
    {
        $this->amount = '';
        $this->durationMonths = '';
        $this->purpose = '';
        $this->employmentStatus = '';
        $this->monthlyIncome = '';
        $this->additionalInfo = '';
        $this->calculatedInterest = 0;
        $this->calculatedMonthlyPayment = 0;
        $this->calculatedTotalPayable = 0;
    }

    public function updatedAmount(): void
    {
        $this->calculateLoan();
    }

    public function calculateLoan()
    {
        if (!$this->durationMonths && $this->amount) {
            $this->errorAlert(message: 'Please enter loan duration in months.');
            return;
        }

        if (!$this->amount || !$this->selectedLoanType) {
            return;
        }

        $principal = floatval($this->amount);
        $months = intval($this->durationMonths);
        $annualRate = floatval($this->selectedLoanType->interest_rate);
        $monthlyRate = $annualRate / 12 / 100;

        // Calculate monthly payment using amortization formula
        if ($monthlyRate > 0) {
            $this->calculatedMonthlyPayment = $principal *
                ($monthlyRate * pow(1 + $monthlyRate, $months)) /
                (pow(1 + $monthlyRate, $months) - 1);
        } else {
            $this->calculatedMonthlyPayment = $principal / $months;
        }

        $this->calculatedTotalPayable = $this->calculatedMonthlyPayment * $months;
        $this->calculatedInterest = $this->calculatedTotalPayable - $principal;
    }

    public function applyForLoan()
    {
        $this->validate([
            'loanTypeId' => 'required|exists:loan_types,id',
            'amount' => [
                'required',
                'numeric',
                'min:' . ($this->selectedLoanType->min_amount ?? 1000),
                'max:' . ($this->selectedLoanType->max_amount ?? 100000),
            ],
            'durationMonths' => [
                'required',
                'integer',
                'min:' . ($this->selectedLoanType->min_duration ?? 1),
                'max:' . ($this->selectedLoanType->max_duration ?? 60),
            ],
            'purpose' => ['required', 'string', 'min:10', 'max:500'],
            'employmentStatus' => ['required', 'string'],
            'monthlyIncome' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $loan = ModelsLoan::create([
                'user_id' => Auth::id(),
                'loan_type_id' => $this->loanTypeId,
                'amount' => $this->amount,
                'interest_rate' => $this->selectedLoanType->interest_rate,
                'duration_months' => $this->durationMonths,
                'monthly_payment' => $this->calculatedMonthlyPayment,
                'total_payable' => $this->calculatedTotalPayable,
                'outstanding_balance' => $this->calculatedTotalPayable,
                'status' => 'pending',
                'purpose' => $this->purpose,
                'employment_status' => $this->employmentStatus,
                'monthly_income' => $this->monthlyIncome,
                'additional_info' => $this->additionalInfo,
            ]);

            $loan->logActivity('application_submitted', 'Loan application submitted');

            $this->successAlert(message: 'Loan application submitted successfully!');

            $this->closeModal();

            $this->activeTab = 'my-loans';
        } catch (\Exception $e) {
            $this->errorAlert(message: 'Failed to submit loan application. Please try again.');
        }
    }
}