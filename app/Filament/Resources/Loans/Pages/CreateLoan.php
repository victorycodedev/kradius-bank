<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Filament\Resources\Loans\LoanResource;
use App\Models\LoanType;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate monthly payment and total payable
        $loanType = LoanType::find($data['loan_type_id']);

        if ($loanType) {
            $monthlyPayment = $loanType->calculateMonthlyPayment($data['amount'], $data['duration_months']);
            $data['monthly_payment'] = $monthlyPayment;
            $data['total_payable'] = $monthlyPayment * $data['duration_months'];
            $data['outstanding_balance'] = $data['total_payable'];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->logActivity(
            'created',
            'Loan application created by admin: ' . Auth::user()->name
        );
    }
}
