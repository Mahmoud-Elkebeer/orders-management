<?php

namespace App\Http\Controllers\API;

use App\Exceptions\DeleteOrderException;
use App\Exceptions\UpdateOrderException;
use Exception;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(public OrderService $orderService){}

    public function index(Request $request)
    {
        $filters = $request->only('status');

        $orders = $this->orderService->getOrders($filters);

        return ApiResponse::success(OrderResource::collection($orders), 'Orders retrieved successfully');
    }

    public function store(CreateOrderRequest $request)
    {
        $order = $this->orderService->createOrder($request->Validated());

        return ApiResponse::success(new OrderResource($order), 'Order created successfully', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $order = $this->orderService->getOrder($id);
        return ApiResponse::success(new OrderResource($order), 'Order created successfully');
    }

    public function update(UpdateOrderRequest $request, $id)
    {
        try {
            $order = $this->orderService->updateOrder($id, $request->validated());

            return ApiResponse::success(new OrderResource($order), 'Order updated successfully');
        } catch (UpdateOrderException $exception){
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
        }catch (DeleteOrderException $exception) {
            return ApiResponse::error($exception->getMessage());
        } catch (Exception $exception) {
            return ApiResponse::error('An unexpected error occurred. Please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
