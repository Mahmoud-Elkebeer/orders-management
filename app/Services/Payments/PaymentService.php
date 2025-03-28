<?php

namespace App\Services\Payments;

use Exception;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\ProcessPaymentException;
use App\Models\Payment;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        public PaymentRepository $paymentRepository,
        public OrderRepository $orderRepository
    ){}

    /**
     * @throws ProcessPaymentException
     * @throws Exception
     */
    public function processPayment(array $data = []): Payment
    {
        $order = $this->orderRepository->getOrderById( $data['order_id']);

        if (!$order) {
            throw new ProcessPaymentException('Order not found.');
        }

        if ($order->status !== OrderStatus::CONFIRMED) {
            throw new ProcessPaymentException('Only confirmed orders can be paid for.');
        }

        $gateway = PaymentGatewayFactory::make($data['payment_method']);

        DB::beginTransaction();
        try {
            $payment = $gateway->processPayment($order, $data);

            if ($payment->status === PaymentStatus::SUCCESSFUL) {
                $order->update(['status' => OrderStatus::PAID]);
            }
            DB::commit();
            return $payment;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function getPayments(?int $orderId = null, $perPage = 10)
    {
        return $this->paymentRepository->getPayments($orderId)->paginate($perPage);
    }
}
