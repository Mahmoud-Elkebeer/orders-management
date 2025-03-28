<?php

namespace Tests\Traits;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

trait JwtTestHelper
{
    protected function authenticateUser($user = null)
    {
        $user = $user ?? User::factory()->create();

        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
            'headers' => [
                'Authorization' => "Bearer $token",
                'Accept' => 'application/json',
            ],
        ];
    }
}
