<?php

namespace App\Services\Payments;

use App\Models\Order;

interface PaymentGatewayInterface {
    public function processPayment(Order $order, array $paymentData);
}
