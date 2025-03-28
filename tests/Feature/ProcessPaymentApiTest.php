<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\ProcessPaymentException;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\JwtTestHelper;
use Mockery;
class ProcessPaymentApiTest extends TestCase
{
    use RefreshDatabase, JwtTestHelper;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->authenticateUser();

        $this->paymentService = Mockery::mock(PaymentService::class);
        $this->app->instance(PaymentService::class, $this->paymentService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_successfully_processes_a_payment()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user['user']->id,
            'status' => OrderStatus::CONFIRMED,
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'user_id' => $this->user['user']->id,
            'amount' => 100,
            'payment_method' => 'paypal',
            'status' => PaymentStatus::SUCCESSFUL,
            'created_at' => now(),
        ]);

        $this->paymentService->shouldReceive('processPayment')
            ->once()
            ->withAnyArgs()
            ->andReturn($payment);

        $response = $this->withHeaders($this->user['headers'])
            ->postJson(route('payments.process'), [
                'order_id' => $order->id,
                'user_id' => $this->user['user']->id,
                'payment_method' => 'paypal',
                'amount' => 100,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'amount',
                    'status',
                    'payment_method',
                    'transaction_id',
                    'created_at',
                    'order'
                ]
            ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'user_id' => $this->user['user']->id,
            'amount' => 100,
            'payment_method' => 'paypal',
            'status' => PaymentStatus::SUCCESSFUL,
        ]);
    }

    /** @test */
    public function it_fails_if_order_is_not_confirmed()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user['user']->id,
            'status' => OrderStatus::PENDING,
        ]);

        $this->paymentService->shouldReceive('processPayment')
            ->once()
            ->withAnyArgs()
            ->andThrow(new ProcessPaymentException('Only confirmed orders can be paid for.'));

        $data = [
            'order_id' => $order->id,
            'payment_method' => 'paypal',
            'amount' => 100.00,
        ];

        $response =  $this->withHeaders($this->user['headers'])
            ->postJson(route('payments.process'), $data);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Only confirmed orders can be paid for.',
            ]);
    }

    /** @test */
    public function it_fails_if_order_does_not_exist()
    {
        $data = [
            'order_id' => 9999,
            'payment_method' => 'paypal',
            'amount' => 100.00,
        ];

        $response =  $this->withHeaders($this->user['headers'])
            ->postJson(route('payments.process'), $data);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'order_id' => [
                    'The selected order id is invalid.'
                ],
            ]);
    }

    /** @test */
    public function it_fails_if_payment_gateway_is_invalid()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user['user']->id,
            'status' => OrderStatus::PENDING,
        ]);

        $data = [
            'order_id' => $order->id,
            'payment_method' => 'invalid_gateway',
            'amount' => 100.00,
        ];

        $response =  $this->withHeaders($this->user['headers'])
            ->postJson(route('payments.process'), $data);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'payment_method' => [
                    'The selected payment method is invalid.'
                ],
            ]);
    }
}
