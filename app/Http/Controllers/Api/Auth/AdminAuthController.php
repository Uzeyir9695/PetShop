<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserForm;
use App\Models\JwtToken;
use App\Traits\JwtTokenTrait;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    use JwtTokenTrait;

    /**
     * @OA\Post(
     *     path="/api/v1/admin/create",
     *     tags={"Admin"},
     *     summary="Register a new admin",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="avatar", type="string", example="123e4567-e89b-12d3-a456-426655440000"),
     *             @OA\Property(property="address", type="string", example="123 Main St."),
     *             @OA\Property(property="phone_number", type="string", example="+1234567890"),
     *             @OA\Property(property="email", type="string", format="email",  uniqueItems=true, example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password"),
     *             @OA\Property(property="is_marketing", type="integer", example=1),
     *         ),
     *     ),
     *     @OA\Response(response="201", description="Admin registered successfully."),
     *     @OA\Response(response="422", description="Validation error.")
     * )
     */

    public function register(UserForm $request)
    {
        $data = $request->validated();
        $registerForm = apiRegister($data);
        $token = $this->generateJwtToken($registerForm[0]);
        return response()->json([
            'message' => 'Successfully registered',
            'admin' => $registerForm[1],
            'token' => $token->toString()
        ], 201);
    }
    /**
     * @OA\Post(
     *     path="/api/v1/admin/login",
     *     tags={"Admin"},
     *     summary="Login an existing admin",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com", description="Admin email"),
     *             @OA\Property(property="password", type="string", format="password", example="password", description="Admin password")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged in"),
     *             @OA\Property(property="user", type="object", example="{ id: 1, firs_name: 'John Doe', email: 'john.doe@example.com', is_admin: 1 }"),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiJ9.eyJ1dWlkIjoiNWZjNTVjNDI4ZmRjMDk4MmJhNjEwNTZhIiwiaXNfYWRtaW4iOiIwIiwiZXhwaXJlcyI6IjIwMjMtMTItMjIgMjA6NDg6NTAiLCJpYXQiOjE2MTkwMzI2NTgsImV4cCI6MTYxOTAzMzI1OH0.6x_AuZInpU6srzfoHevph8V7yp-5pX9cSJbRtmvHJ4k")
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */

    public function login(Request $request)
    {
        $loginForm = apiLogin($request);
        $token = $this->generateJwtToken($loginForm[0]);

        JwtToken::create([
            'user_id' => $loginForm[1]->id,
            'unique_id' => $token->claims()->get('jti'),
            'token_title' => 'API Access Token',
            'restrictions' => null,
            'permissions' => null,
            'expires_at' => null,
            'last_used_at' => null,
            'refreshed_at' => null
        ]);
        return response()->json([
            'message' => 'Successfully logged in',
            'admin' => $loginForm[1],
            'token' => $token->toString()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/logout",
     *     tags={"Admin"},
     *     summary="Logout the authenticated admin",
     *     security={{ "jwt":{} }},
     *     @OA\Response(response="200", description="Successfully logged out"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Token not found"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */

    public function logout(Request $request)
    {
        $token = apiLogout($request);
        try {
            $jwtTokens = JwtToken::where('unique_id', $token->claims()->get('jti'))->first();
            if ($jwtTokens) {
                    $jwtTokens->delete();
                return response()->json(['message' => 'Successfully logged out']);
            } else {
                return response()->json(['message' => 'Token not found'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
