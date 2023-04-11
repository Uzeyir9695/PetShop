<?php

use App\Models\JwtToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use \Illuminate\Http\Request;

function authUser($token) {
    $config = Configuration::forAsymmetricSigner(
        new Sha256(),
        InMemory::plainText(file_get_contents(base_path('keys/public.key'))),
        InMemory::plainText(file_get_contents(base_path('keys/private.key'))),
    );
    try {
        $token = $config->parser()->parse($token);
        $constraints = [
            new IssuedBy(config('app.url')),
            new PermittedFor(config('app.url')),
            new SignedWith($config->signer(), $config->signingKey()),
        ];

        $config->validator()->assert($token, ...$constraints);
        $uuid = $token->claims()->get('user_data')['uuid'];
        $user = User::where('uuid', $uuid)->first();// get user from database based on $uuid
        if (!$user) {
            print_r('Unauthenticated');
            exit();
        }
        $jwtToken = JwtToken::where('unique_id', $token->claims()->get('jti'))->first();
        // check if a user is authorized
        if(request()->segment('4') != 'login' && request()->segment('4') != 'create') {
            if ((!$token->claims()->get('user_data')['authorized']) || $jwtToken == null) {
                print_r('Unauthorized');
                exit();
            }
        }

        return $user;

    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 401);
    }
}

function apiRegister($data)
{

    $data['uuid'] = \Illuminate\Support\Str::uuid();
    $data['password'] = Hash::make($data['password']);

    if (request()->segment(3) === 'admin') {
        $data['is_admin'] = 1;
    }

    $user = User::create($data);
    $data = ['uuid' => $user->uuid, 'authorized' => false];

    $data = [$data, $user];
    return $data;
}

function apiLogin(Request $request)
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
    $data = [$data, $user];
    return $data;
}

function apiLogout(Request $request)
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

    return  $token;
}
