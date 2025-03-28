<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Services\OrderService;
use Tests\TestCase;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\JwtTestHelper;

class OrderApiTest extends TestCase
{
    use RefreshDatabase, JwtTestHelper;

    protected $user;
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->authenticateUser();
    }

    /** @test */
    public function it_can_create_order()
    {
        $data['items'] = [
            ['name' => 'item 1', 'quantity' => 1, 'price' => 100],
            ['name' => 'item 2', 'quantity' => 2, 'price' => 50]
        ];

        $response = $this->withHeaders($this->user['headers'])->postJson('/api/orders', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data' => ['id', 'status', 'amount', 'items']]);
    }

    /** @test */
    public function it_can_show_order()
    {
        $order = Order::factory()->create();

        $response = $this->withHeaders($this->user['headers'])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data' => ['id', 'status', 'amount', 'items']]);
    }

    /** @test */
    public function it_can_update_order()
    {
        $order = Order::factory()->create(['status' => OrderStatus::PENDING, 'user_id' => $this->user['user']->id]);
        $updateData = ['status' => OrderStatus::CANCELLED];

        $response = $this->withHeaders($this->user['headers'])->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => OrderStatus::getLabel(OrderStatus::CANCELLED)]);
    }

    /** @test */
    public function it_update_order_fails_on_exception()
    {
        $this->mock(OrderService::class, function ($mock) {
            $mock->shouldReceive('updateOrder')->andThrow(new \Exception('Unexpected error'));
        });

        $order = Order::factory()->create();
        $updateData = ['status' => OrderStatus::CANCELLED];

        $response = $this->withHeaders($this->user['headers'])->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(500)
            ->assertJsonFragment(['message' => 'An unexpected error occurred. Please try again later.']);
    }

    /** @test */
    public function it_can_delete_order()
    {
        $order = Order::factory()->create(['user_id' => $this->user['user']->id]);

        $response = $this->withHeaders($this->user['headers'])->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Order deleted successfully']);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /** @test */
    public function it_delete_order_fails_as_payment_exists()
    {
        $order = Order::factory()->create(['user_id' => $this->user['user']->id]);
        Payment::factory()->create(['user_id' => $this->user['user']->id, 'order_id' => $order->id]);

        $response = $this->withHeaders($this->user['headers'])->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'Cannot delete an order with associated payments.']);
    }
}
