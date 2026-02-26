<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $symbols = ['AAPL', 'GOOGL', 'MSFT', 'TSLA', 'AMZN', 'META', 'NFLX', 'NVDA'];
        $names = ['Apple Inc', 'Alphabet Inc', 'Microsoft Corp', 'Tesla Inc', 'Amazon.com Inc', 'Meta Platforms', 'Netflix Inc', 'NVIDIA Corp'];

        $index = array_rand($symbols);
        $price = fake()->randomFloat(2, 50, 500);
        $change = fake()->randomFloat(2, -20, 20);

        return [
            'symbol' => $symbols[$index],
            'name' => $names[$index],
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['Technology', 'Finance', 'Healthcare', 'Energy']),
            'current_price' => $price,
            'previous_close' => $price - $change,
            'price_change' => $change,
            'price_change_percentage' => ($change / ($price - $change)) * 100,
            'minimum_investment' => 1000,
            'maximum_investment' => 1000000,
            'is_active' => true,
            'is_featured' => fake()->boolean(30),
        ];
    }
}
