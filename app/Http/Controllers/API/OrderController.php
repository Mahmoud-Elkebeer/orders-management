<?php

namespace App\Http\Controllers\API;

use App\Exceptions\OrderException;
use Exception;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(public OrderService $orderService){}

    public function index(Request $request)
    {
        try {
            $filters = $request->only('status');

            $filters['user_id'] = Auth::id();

            $orders = $this->orderService->getOrders($filters);

            return ApiResponse::success(OrderResource::collection($orders), 'Orders retrieved successfully');
        } catch (Exception $exception) {
            return ApiResponse::error('An unexpected error occurred. Please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(CreateOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->Validated());

            return ApiResponse::success(new OrderResource($order), 'Order created successfully', Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return ApiResponse::error('An unexpected error occurred. Please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @throws OrderException
     */
    public function show($id)
    {
        try {
            $order = $this->orderService->getOrder($id);

            if (Auth::id() !== $order->user_id) {
                throw new OrderException('You are not authorized to access this order.');
            }

            return ApiResponse::success(new OrderResource($order), 'Order retrieved successfully');
        } catch (OrderException $exception){
            return ApiResponse::error($exception->getMessage());
        } catch (Exception $exception) {
            return ApiResponse::error('An unexpected error occurred. Please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateOrderRequest $request, $id)
    {
        try {
            $order = $this->orderService->updateOrder($id, $request->validated());

            return ApiResponse::success(new OrderResource($order), 'Order updated successfully');
        } catch (OrderException $exception){
            return ApiResponse::error($exception->getMessage());
        } catch (Exception $exception) {
            return ApiResponse::error('An unexpected error occurred. Please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $this->orderService->deleteOrder($id);
            return ApiResponse::success(null, 'Order deleted successfully');
        }catch (OrderException $exception) {
            return ApiResponse::error($exception->getMessage());
        } catch (Exception $exception) {
            return ApiResponse::error('An unexpected error occurred. Please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
