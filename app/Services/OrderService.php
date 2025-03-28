<?php

namespace App\Services;

use Exception;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Auth;
use App\Repositories\OrderRepository;
use App\Exceptions\DeleteOrderException;
use App\Exceptions\OrderException;

class OrderService
{
    protected OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getOrders($filters = [], $perPage = 10)
    {
        return $this->orderRepository->getAllOrders($filters)->paginate($perPage);
    }

    public function getOrder($id)
    {
        return $this->orderRepository->getOrderById($id);
    }

    public function createOrder(array $data): Order
    {
        return $this->orderRepository->createOrder($data);
    }

    /**
     * @throws Exception
     */
    public function updateOrder($id, array $data)
    {
        $order = $this->orderRepository->getOrderById($id);
        if ($order->status !== OrderStatus::PENDING) {
            throw new OrderException('Only pending orders can be updated.');
        }
        if (Auth::id() !== $order->user_id) {
            throw new OrderException('You are not authorized to update this order.');
        }

        return $this->orderRepository->updateOrder($order, $data);
    }

    /**
     * @throws OrderException
     */
    public function deleteOrder($id)
    {
        $order = $this->orderRepository->getOrderById($id);

        if ($order->payments()->exists()) {
            throw new OrderException("Cannot delete an order with associated payments.");
        }
        if (Auth::id() !== $order->user_id) {
            throw new OrderException('You are not authorized to delete this order.');
        }

        $this->orderRepository->deleteOrder($order);
    }
}
