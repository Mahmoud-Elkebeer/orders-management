<?php

namespace App\Services;

use Exception;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Auth;
use App\Repositories\OrderRepository;
use App\Exceptions\DeleteOrderException;
use App\Exceptions\UpdateOrderException;

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
            throw new UpdateOrderException('Only pending orders can be updated.');
        }
        if (Auth::id() !== $order->user_id) {
            throw new UpdateOrderException('You are not authorized to update this order.');
        }

        return $this->orderRepository->updateOrder($order, $data);
    }

    /**
     * @throws DeleteOrderException
     */
    public function deleteOrder($id)
    {
        $order = $this->orderRepository->getOrderById($id);

        if ($order->payments()->exists()) {
            throw new DeleteOrderException("Cannot delete an order with associated payments.");
        }

        $this->orderRepository->deleteOrder($order);
    }
}
