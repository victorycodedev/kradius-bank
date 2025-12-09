<?php

namespace App\Console\Commands;

use App\Models\Investment;
use App\Models\InvestmentSetting;
use Illuminate\Console\Command;

class ProcessInvestmentProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investments:process-profits';



    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and distribute investment profits';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = InvestmentSetting::get();

        if (!$settings->auto_profit_enabled) {
            $this->info('Auto-profit is disabled');
            return;
        }

        $investments = Investment::where('status', 'active')
            ->with(['stock', 'userAccount'])
            ->get();

        $count = 0;

        foreach ($investments as $investment) {
            // Calculate profit based on ROI percentage
            $roiAmount = ($investment->amount * ($investment->roi_percentage ?? $settings->default_roi_percentage)) / 100;

            // Adjust based on frequency
            if ($settings->auto_profit_frequency === 'monthly') {
                $roiAmount = $roiAmount / 12;
            } elseif ($settings->auto_profit_frequency === 'weekly') {
                $roiAmount = $roiAmount / 52;
            }

            // Create profit entry
            $profit = $investment->addProfit(
                $roiAmount,
                'roi',
                'Automated ROI distribution',
                true
            );

            // Auto-pay the profit
            $investment->payProfit($profit);

            $count++;
        }

        $this->info("Processed {$count} investment profits");
    }
}
