<?php

namespace App\Livewire\MobileApp\Screen;

use App\Models\Loan as ModelsLoan;
use App\Models\LoanSetting;
use App\Models\LoanType;
use App\Models\Settings;
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
    public $terms;

    // Calculated fields
    public $calculatedInterest = 0;
    public $calculatedMonthlyPayment = 0;
    public $calculatedTotalPayable = 0;

    public function mount(): void
    {
        $config = LoanSetting::find(1);
        abort_if(!$config->loan_applications_enabled, 404);
        $this->monthsList = range(1, 12);
        $loanSet = LoanSetting::find(1);

        $this->terms = $loanSet->terms_and_conditions;
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
            'loanTypeId' => ['required', 'exists:loan_types,id'],
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

        // count the number of active loans
        $activeLoans = ModelsLoan::where('user_id', Auth::user()->id)
            ->where('status', 'active')
            ->count();
        $settings = LoanSetting::find(1);

        if ($activeLoans >= $settings->max_active_loans_per_user) {
            $this->errorAlert(message: 'You have reached the maximum number of active loans.');
            return;
        }

        if ($settings->min_account_age_days > 0) {
            // min_account_age_days
            $accountAge = Auth::user()->created_at->diffInDays();
            if ($accountAge < $settings->min_account_age_days) {
                $this->errorAlert(message: 'Your account is too young to apply for a loan.');
                return;
            }
        }

        // min_account_balance
        if ($settings->min_account_balance > 0) {
            $userAccounts = Auth::user()->accounts()->get();
            $totalBalance = $userAccounts->sum('balance');
            if ($totalBalance < $settings->min_account_balance) {
                $this->errorAlert(message: 'Your account balance is too low to apply for a loan.');
                return;
            }
        }

        if ($settings->require_guarantor && Auth::user()->kyc_status != 'verified') {
            $this->errorAlert(message: 'Please verify your KYC to proceed.');
            return;
        }

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
