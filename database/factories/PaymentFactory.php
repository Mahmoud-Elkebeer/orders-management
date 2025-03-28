<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'order_id' => Order::factory()->create()->id,
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'successful', 'failed']),
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal']),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'transaction_id' => $this->faker->uuid,
        ];
    }
}
