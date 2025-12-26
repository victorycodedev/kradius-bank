<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Loan;
use App\Models\Investment;
use App\Models\LoanRepayment;
use App\Models\UserAccount;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Banking
        $totalDeposits = Transaction::where('transaction_type', 'deposit')->sum('amount');
        $totalTransfers = Transaction::where('transaction_type', 'transfer')->sum('amount');

        // Loans
        $totalLoanIssued = Loan::sum('amount');
        $loanRepaid = LoanRepayment::sum('amount');
        $loanOutstanding = $totalLoanIssued - $loanRepaid;

        // Investments
        $totalInvested = Investment::sum('amount');
        $totalRoiPaid = Investment::sum('total_profit_paid');

        return [
            // ---- USERS ----
            Stat::make('Total Users', User::count())
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make(
                'Active Users (30d)',
                User::where('last_login_at', '>=', now()->subDays(30))->count()
            )
                ->icon('heroicon-o-bolt')
                ->color('success'),

            // ---- BANKING ----
            Stat::make('Total Balance', '₦' . number_format(UserAccount::sum('balance'), 2))
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make(
                'Total Amount Transferred',
                '₦' . number_format($totalTransfers, 2)
            )
                ->icon('heroicon-o-scale')
                ->color('danger'),

            // ---- LOANS ----
            Stat::make('Loans Issued', '₦' . number_format($totalLoanIssued, 2))
                ->icon('heroicon-o-building-library')
                ->color('primary'),

            Stat::make('Outstanding Loans', '₦' . number_format($loanOutstanding, 2))
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),

            Stat::make(
                'Active Loans',
                Loan::where('status', 'active')->count()
            )
                ->icon('heroicon-o-clock')
                ->color('warning'),

            // ---- INVESTMENTS ----
            Stat::make('Capital Invested', '₦' . number_format($totalInvested, 2))
                ->icon('heroicon-o-chart-bar')
                ->color('success'),

            Stat::make('ROI Paid', '₦' . number_format($totalRoiPaid, 2))
                ->icon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make(
                'Active Investments',
                Investment::where('status', 'active')->count()
            )
                ->icon('heroicon-o-presentation-chart-line')
                ->color('primary'),
        ];
    }
}
