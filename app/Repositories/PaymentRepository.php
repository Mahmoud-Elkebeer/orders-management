<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository
{
    public function getPayments(?int $userId = null, ?int $orderId = null)
    {
        $query = Payment::with('order');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($orderId) {
            $query->where('order_id', $orderId);
        }

        return $query;
    }

    public function createPayment(array $data): Payment
    {
       return Payment::create([
            'order_id' => $data['order_id'],
            'user_id' => $data['user_id'],
            'status' => $data['status'],
            'payment_method' => $data['payment_method'],
            'transaction_id' => $data['transaction_id'],
            'amount' => $data['amount'],
        ]);
    }
}
