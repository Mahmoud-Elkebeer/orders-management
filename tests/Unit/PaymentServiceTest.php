<?php

namespace Tests\Unit;

use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use Mockery;
use App\Enums\OrderStatus;
use App\Exceptions\ProcessPaymentException;
use App\Repositories\OrderRepository;
use Tests\TestCase;
use App\Models\Order;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $paymentService;

    protected $paymentRepository;

    protected $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
        $this->paymentRepository = Mockery::mock(PaymentRepository::class);
        $this->orderRepository = Mockery::mock(OrderRepository::class);

        $this->paymentService = new PaymentService(
            $this->paymentRepository,
            $this->orderRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_fetch_all_payments_with_pagination()
    {
        $payments = Payment::factory()->count(10)->create();

        $mockPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $payments,
            10,
            10,
            1
        );

        // Mock the repository to return a query builder mock
        $queryMock = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);

        $this->paymentRepository->shouldReceive('getPayments')
            ->andReturn($queryMock);

        $queryMock->shouldReceive('paginate')
            ->with(10)
            ->andReturn($mockPaginator);

        $result = $this->paymentService->getPayments();

        $this->assertCount(10, $result->items());
    }

    /** @test */
    public function it_can_fetch_payments_for_a_specific_order_with_pagination()
    {
        $perPage = 3;
        $order = Order::factory()->create();
        $payments = Payment::factory()->count(10)->create(['order_id' => $order->id]);

        $paginatedItems = $payments->slice(0, $perPage)->values();

        $mockPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            10,
            $perPage,
            1
        );
        $queryMock = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);

        $this->paymentRepository->shouldReceive('getPayments')
            ->andReturn($queryMock);

        $queryMock->shouldReceive('paginate')
            ->with($perPage)
            ->andReturn($mockPaginator);

        $result = $this->paymentService->getPayments(null, $order->id, 3);

        $this->assertCount(3, $result->items());
    }

    /** @test */
    public function process_payment_throws_exception_if_order_is_not_confirmed()
    {
        $order = Order::factory()->make([
            'status' => OrderStatus::PENDING,
        ]);

        $this->orderRepository->shouldReceive('getOrderById')
            ->with($order->id)
            ->andReturn($order);

        $this->expectException(ProcessPaymentException::class);
        $this->expectExceptionMessage('Only confirmed orders can be paid for.');

        $this->paymentService->processPayment([
            'order_id' => $order->id,
            'payment_method' => 'paypal',
            'amount' => 100.00,
        ]);
    }

    /** @test */
    public function it_processes_payment_successfully()
    {
        $order = Order::factory()->make([
            'id' => 1,
            'status' => OrderStatus::CONFIRMED,
        ]);

        $payment = Payment::factory()->make([
            'order_id' => $order->id,
            'status' => PaymentStatus::SUCCESSFUL,
        ]);

        $this->orderRepository->shouldReceive('getOrderById')
            ->with($order->id)
            ->andReturn($order);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        $gateway = Mockery::mock(\App\Services\Payments\PaymentGatewayInterface::class);
        $gateway->shouldReceive('processPayment')->andReturn($payment);

        $factoryMock = Mockery::mock('alias:App\Services\Payments\PaymentGatewayFactory');
        $factoryMock->shouldReceive('make')
            ->with('paypal')
            ->andReturn($gateway);

        $this->paymentService->processPayment([
            'order_id' => $order->id,
            'payment_method' => 'paypal',
            'amount' => 100.00,
        ]);

        $this->assertEquals($payment->status, PaymentStatus::SUCCESSFUL);
    }
}
