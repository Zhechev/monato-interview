<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'type' => $this->faker->randomElement(['purchase', 'sale']),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'completed',
            'balance_after' => $this->faker->randomFloat(2, 0, 5000)
        ];
    }

    public function purchase(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'purchase'
            ];
        });
    }

    public function sale(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'sale'
            ];
        });
    }
}
