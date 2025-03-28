<?php

namespace App\Services\Payments\Gateways;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayInterface;

class CreditCardPayment implements PaymentGatewayInterface {
    public function processPayment(Order $order, array $paymentData): Payment
    {
        $transactionId = 'CC-' . strtoupper(uniqid());

        return Payment::create([
            'order_id' => $order->id,
            'status' => PaymentStatus::SUCCESSFUL,
            'payment_method' => 'credit_card',
            'transaction_id' => $transactionId,
        ]);
    }
}
