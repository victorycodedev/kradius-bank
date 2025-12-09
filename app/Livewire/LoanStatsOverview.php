<?php

namespace App\Livewire;

use App\Models\Loan;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoanStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending Applications', Loan::where('status', 'pending')->count())
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Approved Loans', Loan::where('status', 'approved')->count())
                ->description('Ready for disbursement')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([3, 5, 6, 7, 8, 10, 12]),

            Stat::make('Active Loans', Loan::whereIn('status', ['disbursed', 'active'])->count())
                ->description('Currently being repaid')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info')
                ->chart([15, 14, 16, 18, 20, 19, 21]),

            Stat::make('Total Disbursed', '₦' . number_format(
                Loan::whereIn('status', ['disbursed', 'active', 'completed'])
                    ->sum('approved_amount'),
                2
            ))
                ->description('All time')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Outstanding Balance', '₦' . number_format(
                Loan::whereIn('status', ['disbursed', 'active'])
                    ->sum('outstanding_balance'),
                2
            ))
                ->description('Total amount to be repaid')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make('Defaulted Loans', Loan::where('status', 'defaulted')->count())
                ->description('Requires attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->chart([1, 1, 2, 1, 0, 1, 2]),
        ];
    }
}
