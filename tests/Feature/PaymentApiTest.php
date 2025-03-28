<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\JwtTestHelper;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase, JwtTestHelper;

    protected $user;
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->authenticateUser();
    }

    /** @test */
    public function it_can_fetch_all_payments()
    {
        Payment::factory()->count(5)->create(['user_id' => $this->user['user']->id]);

        $response = $this->withHeaders($this->user['headers'])->getJson(route('payments.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'amount','status', 'payment_method', 'transaction_id', 'created_at'],
                ]
            ])
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function it_can_fetch_payments_for_a_specific_order()
    {
        $order = Order::factory()->create(['user_id' => $this->user['user']->id]);
        Payment::factory()->count(3)->create(['user_id' => $this->user['user']->id, 'order_id' => $order->id]);

        $response = $this->withHeaders($this->user['headers'])
            ->getJson(route('payments.index', ['order_id' => $order->id]));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_returns_empty_array_if_no_payments_found()
    {
        $response = $this->withHeaders($this->user['headers'])
            ->getJson(route('payments.index', ['order_id' => 9999]));

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
