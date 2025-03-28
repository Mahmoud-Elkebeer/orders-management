<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository
{
    public function getPayments(?int $orderId = null)
    {
        $query = Payment::with('order');

        if ($orderId) {
            $query->where('order_id', $orderId);
        }

        return $query;
    }
}
