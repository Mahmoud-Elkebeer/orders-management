<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Exceptions\ProcessPaymentException;
use App\Http\Requests\ProcessPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function __construct(public PaymentService $paymentService){}

    public function index(Request $request)
    {
        $payments = $this->paymentService->getPayments($request->query('order_id'));

        return ApiResponse::success(PaymentResource::collection($payments), 'Payments retrieved successfully');
    }

    public function process(ProcessPaymentRequest $request)
    {
        try {
            $payment = $this->paymentService->processPayment($request->validated());

            return ApiResponse::success(new PaymentResource($payment), 'Payment processed successfully');
        } catch (ProcessPaymentException $exception) {
            return ApiResponse::error($exception->getMessage());
        }
        catch (Exception $exception) {
            return ApiResponse::error('An unexpected error occurred. Please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
