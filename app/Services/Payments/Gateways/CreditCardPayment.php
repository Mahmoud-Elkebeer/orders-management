<?php

namespace App\Services\Payments\Gateways;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\PaymentService;

class CreditCardPayment implements PaymentGatewayInterface {

    public function __construct(
        public PaymentService $paymentService,
    ){}

    public function processPayment(Order $order, array $paymentData): Payment
    {
        $paymentData['transaction_id'] = 'CC-' . strtoupper(uniqid());
        $paymentData['order_id'] = $order->id;
        $paymentData['payment_method'] = 'credit_card';
        $paymentData['status'] = PaymentStatus::SUCCESSFUL;

        return $this->paymentService->createPayment($paymentData);
    }
}
