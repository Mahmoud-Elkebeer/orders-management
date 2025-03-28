<?php

namespace App\Services\Payments\Gateways;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayInterface;

class PaypalPayment implements PaymentGatewayInterface {
    public function processPayment(Order $order, array $paymentData): Payment
    {
        // Simulate PayPal Payment
        $transactionId = 'PP-' . strtoupper(uniqid());

        return Payment::create([
            'order_id' => $order->id,
            'status' => PaymentStatus::SUCCESSFUL,
            'payment_method' => 'paypal',
            'transaction_id' => $transactionId,
        ]);
    }
}
