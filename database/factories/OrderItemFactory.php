<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition()
    {
        return [
            'order_id' => Order::factory(),
            'name' => $this->faker->word,
            'quantity' => $this->faker->numberBetween(1, 10),
            'price' => $this->faker->randomFloat(2, 5, 500),
        ];
    }
}
