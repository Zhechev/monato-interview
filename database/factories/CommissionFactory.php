<?php

namespace Database\Factories;

use App\Models\Commission;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 100),
            'percentage' => $this->faker->randomFloat(2, 5, 15),
            'status' => 'completed'
        ];
    }
}
