<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\JwtToken;
use App\Models\User;
use App\Traits\JwtTokenTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class AuthController extends Controller
{
    use JwtTokenTrait;

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['uuid'] = \Illuminate\Support\Str::uuid();
        $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);

        if ($request->segment(3) === 'admin') {
            $data['is_admin'] = 1;
        }
        $user = User::create($data);
        $data = ['uuid' => $user->uuid];
        $token = $this->generateJwtToken($data);

        return response()->json([
            'message' => 'Successfully registered',
            'user' => $user,
            'token' => $token->toString()
        ], 201);
    }

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
            $jwtToken = JwtToken::where('unique_id', $token->claims()->get('jti'))->first();
            if ($jwtToken) {
                $jwtToken->delete();
                return response()->json(['message' => 'Successfully logged out']);
            } else {
                return response()->json(['message' => 'Token not found'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
