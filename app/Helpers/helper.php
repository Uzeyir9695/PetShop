<?php

use App\Models\JwtToken;
use App\Models\User;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

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
