<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function getAllOrders(array $filters)
    {
        return Order::with('items')
            ->when(isset($filters['status']), fn($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['user_id']), fn($query) => $query->where('user_id', $filters['user_id']));
    }


    public function getOrderById($id)
    {
        return Order::findOrFail($id);
    }

    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'user_id' => $data['user_id'],
                'amount' => collect($data['items'])->sum(fn($item) => $item['quantity'] * $item['price']),
                'status' => OrderStatus::PENDING,
            ]);

            $order->items()->createMany($data['items']);

            return $order->load('items');
        });
    }

    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            if (isset($data['items'])) {
                $amount = collect($data['items'])->sum(fn($item) => $item['quantity'] * $item['price']);
                $order->items()->delete();
                $order->items()->createMany($data['items']);
            }

            $order->update([
                'amount' => $amount ?? $order->amount,
                'status' => $data['status'] ?? OrderStatus::PENDING,
            ]);

            return $order->load('items');
        });
    }


    public function deleteOrder(Order $order)
    {
        $order->delete();
    }
}
