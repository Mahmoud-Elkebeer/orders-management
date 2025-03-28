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
            ->when(isset($filters['status']), fn($query) => $query->where('status', $filters['status']));
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
                'total' => collect($data['items'])->sum(fn($item) => $item['quantity'] * $item['price']),
                'status' => OrderStatus::PENDING,
            ]);

            $order->items()->createMany($data['items']);

            return $order->load('items');
        });
    }

    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $total = collect($data['items'])->sum(fn($item) => $item['quantity'] * $item['price']);

            $order->update([
                'total' => $total,
                'status' => $data['status'] ?? OrderStatus::PENDING,
            ]);

            $order->items()->delete();
            $order->items()->createMany($data['items']);

            return $order->load('items');
        });
    }


    public function deleteOrder(Order $order)
    {
        $order->delete();
    }
}
