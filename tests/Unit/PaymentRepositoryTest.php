<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $paymentRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentRepository = new PaymentRepository();
    }

    /** @test */
    public function it_can_get_all_payments()
    {
        Payment::factory()->count(4)->create();

        $payments = $this->paymentRepository->getPayments();

        $this->assertCount(4, $payments->get());
    }

    /** @test */
    public function it_can_get_payments_by_order_id()
    {
        $order = Order::factory()->create();
        Payment::factory()->count(2)->create(['order_id' => $order->id]);

        $payments = $this->paymentRepository->getPayments(null, $order->id);

        $this->assertCount(2, $payments->get());
    }

    /** @test */
    public function it_creates_a_payment_successfully()
    {
        $data = [
            'order_id' => 1,
            'user_id' => 1,
            'status' => 'successful',
            'payment_method' => 'paypal',
            'transaction_id' => 'txn_123456',
            'amount' => 100.00,
        ];

        $payment = $this->paymentRepository->createPayment($data);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertDatabaseHas('payments', [
            'order_id' => $data['order_id'],
            'user_id' => $data['user_id'],
            'status' => $data['status'],
            'payment_method' => $data['payment_method'],
            'transaction_id' => $data['transaction_id'],
            'amount' => $data['amount'],
        ]);
    }
}
