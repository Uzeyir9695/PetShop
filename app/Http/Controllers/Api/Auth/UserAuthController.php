<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserForm;
use App\Models\JwtToken;
use App\Models\User;
use App\Traits\JwtTokenTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class UserAuthController extends Controller
{
    use JwtTokenTrait;

    /**
     * @OA\Post(
     *     path="/api/v1/user/create",
     *     tags={"Users"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="avatar", type="string", example="123e4567-e89b-12d3-a456-426655440000"),
     *             @OA\Property(property="address", type="string", example="123 Main St."),
     *             @OA\Property(property="phone_number", type="string", example="+1234567890"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password"),
     *             @OA\Property(property="is_marketing", type="integer", example=1),
     *         ),
     *     ),
     *     @OA\Response(response="201", description="User registered successfully."),
     *     @OA\Response(response="422", description="Validation error.")
     * )
     */
    public function register(UserForm $request)
    {
        $data = $request->validated();

        $data['uuid'] = \Illuminate\Support\Str::uuid();
        $data['password'] = Hash::make($data['password']);

        if ($request->segment(3) === 'admin') {
            $data['is_admin'] = 1;
        }
        $user = User::create($data);
        $data = ['uuid' => $user->uuid, 'authorized' => false];

        $token = $this->generateJwtToken($data);

        return response()->json([
            'message' => 'Successfully registered',
            'user' => $user,
            'token' => $token->toString()
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/login",
     *     tags={"Users"},
     *     summary="Login an existing user",
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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|max:255',
        ]);

        $credentials = $request->only(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user = Auth::user();

        $data = [
            'uuid' => $user->uuid,
            'authorized' => true,
            'is_admin' => $user->is_admin,
            'expires_at' => \DateTimeImmutable::createFromFormat('U', time() + config('jwt.ttl')),
        ];
        $token = $this->generateJwtToken($data);

        JwtToken::create([
            'user_id' => $user->id,
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
            'user' => $user,
            'token' => $token->toString()
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/user/logout",
     *     tags={"Users, Admin"},
     *     summary="Logout the authenticated user",
     *     security={{ "jwt":{} }},
     *     @OA\Response(response="200", description="Successfully logged out"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Token not found"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */
    public function logout(Request $request)
    {
        $tokenString = $request->bearerToken();
        if (!$tokenString) {
            return response()->json(['message' => 'Token not found'], 404);
        }

        $configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText(file_get_contents(base_path('keys/private.key'))),
            InMemory::plainText(file_get_contents(base_path('keys/public.key'))),
        );

        $token = $configuration->parser()->parse($tokenString);

        try {
            $jwtTokens = JwtToken::where('unique_id', $token->claims()->get('jti'))->get();
            if ($jwtTokens) {
                foreach ($jwtTokens as $jwtToken) {
                    $jwtToken->delete();
                }
                return response()->json(['message' => 'Successfully logged out']);
            } else {
                return response()->json(['message' => 'Token not found'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
