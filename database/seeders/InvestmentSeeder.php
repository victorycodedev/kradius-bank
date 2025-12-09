<?php

namespace Database\Seeders;

use App\Models\InvestmentSetting;
use App\Models\Stock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvestmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create stocks
        $stocks = [
            ['symbol' => 'AAPL', 'name' => 'Apple Inc', 'current_price' => 175.50, 'category' => 'Technology'],
            ['symbol' => 'GOOGL', 'name' => 'Alphabet Inc', 'current_price' => 140.25, 'category' => 'Technology'],
            ['symbol' => 'MSFT', 'name' => 'Microsoft Corp', 'current_price' => 380.00, 'category' => 'Technology'],
            ['symbol' => 'TSLA', 'name' => 'Tesla Inc', 'current_price' => 245.75, 'category' => 'Automotive'],
            ['symbol' => 'AMZN', 'name' => 'Amazon.com Inc', 'current_price' => 155.50, 'category' => 'E-commerce'],
        ];

        foreach ($stocks as $stock) {
            Stock::create(array_merge($stock, [
                'description' => 'Leading company in ' . $stock['category'],
                'minimum_investment' => 1000,
                'is_active' => true,
                'is_featured' => true,
                'previous_close' => $stock['current_price'] * 0.98,
                'price_change' => $stock['current_price'] * 0.02,
                'price_change_percentage' => 2.0,
            ]));
        }

        // Create investment settings
        InvestmentSetting::create([
            'investments_enabled' => true,
            'minimum_investment_amount' => 1000,
            'maximum_investment_amount' => 10000000,
            'auto_profit_enabled' => false,
            'default_roi_percentage' => 5,
            'default_investment_duration_days' => 365,
        ]);
    }
}
