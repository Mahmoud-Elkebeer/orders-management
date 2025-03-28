<?php

namespace App\Http\Responses;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiResponse
{
    public static function success($data = null, string $message = null, int $status = Response::HTTP_OK): JsonResponse
    {
        $result = [];

        if ($data) {
            $result['data'] = $data;
        }

        if ($message) {
            $result['message'] = $message;
        }

        return response()->json($result, $status);
    }

    public static function error(string $message = 'Something went wrong', int $status = Response::HTTP_BAD_REQUEST, $errors = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
