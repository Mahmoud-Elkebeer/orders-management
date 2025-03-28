<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'paid', 'cancelled']),
            'amount' => $this->faker->randomFloat(2, 50, 5000),
        ];
    }
}
