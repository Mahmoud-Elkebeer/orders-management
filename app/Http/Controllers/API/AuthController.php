<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\UserResource;
use Exception;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function __construct(public AuthService $authService){}

    public function register(RegisterRequest $request)
    {
        try {
            $result = $this->authService->createUserAndGenerateToken($request->validated());

            return response()->json([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ], 201);
        } catch (Exception $e) {
            Log::error('Registration failed', [
                'error_message' => $e->getMessage(),
                'error_stack' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Registration failed',
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'user' => new UserResource(auth()->user()),
            'token' => $token
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }
}
