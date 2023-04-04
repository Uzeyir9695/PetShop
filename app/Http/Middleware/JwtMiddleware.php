<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText(file_get_contents(base_path('keys/public.key'))),
            InMemory::plainText(file_get_contents(base_path('keys/private.key'))),
        );
        try {
            $token = $config->parser()->parse($request->bearerToken());
            $constraints = [
                new IssuedBy(config('app.url')),
                new PermittedFor(config('app.url')),
                new SignedWith($config->signer(), $config->signingKey()),
            ];

            $config->validator()->validate($token, ...$constraints);
            $uuid = $token->claims()->get('jti');
            $user = User::where('uuid', $uuid)->first();// get user from database based on $uuid

            if (!$user) {
                throw new \Exception('Unauthenticated');
            }

            // Check if the user is an admin
            if ($user->is_admin) {
                // If the user is an admin, allow access to all routes with the "admin" prefix
                if ($request->routeIs('admin.*')) {
                    return $next($request);
                } else {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            } else {
                // If the user is not an admin, allow access to all routes with the "user" prefix
                if ($request->routeIs('user.*')) {
                    return $next($request);
                } else {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            }

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }
}
