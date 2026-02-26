<?php

namespace Database\Seeders;

use App\Models\LoanType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loanTypes = [
            [
                'name' => 'Personal Loan',
                'description' => 'Quick personal loans for any purpose',
                'min_amount' => 10000,
                'max_amount' => 500000,
                'interest_rate' => 15.0,
                'min_duration_months' => 3,
                'max_duration_months' => 12,
                'is_active' => true,
                'requirements' => ['Valid ID', 'Proof of Income'],
            ],
            [
                'name' => 'Business Loan',
                'description' => 'Loans for business expansion and operations',
                'min_amount' => 50000,
                'max_amount' => 2000000,
                'interest_rate' => 12.0,
                'min_duration_months' => 6,
                'max_duration_months' => 24,
                'is_active' => true,
                'requirements' => ['Business Registration', 'Bank Statements', 'Valid ID'],
            ],
            [
                'name' => 'Emergency Loan',
                'description' => 'Fast loans for emergencies',
                'min_amount' => 5000,
                'max_amount' => 100000,
                'interest_rate' => 18.0,
                'min_duration_months' => 1,
                'max_duration_months' => 6,
                'is_active' => true,
                'requirements' => ['Valid ID'],
            ],
            [
                'name' => 'Salary Advance',
                'description' => 'Advance on your next salary',
                'min_amount' => 10000,
                'max_amount' => 300000,
                'interest_rate' => 10.0,
                'min_duration_months' => 1,
                'max_duration_months' => 3,
                'is_active' => true,
                'requirements' => ['Valid ID', 'Employment Letter'],
            ],
        ];

        foreach ($loanTypes as $type) {
            LoanType::create($type);
        }
    }
}
