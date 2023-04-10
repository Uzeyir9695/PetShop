<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if(!$token) {
            return \response()->json(['message' => 'Please provide a token.']);
        }
        $user = authUser($token);
        // Check if the user is an admin
        if ($user->is_admin) {
            // If the user is an admin, allow access to all routes with the "admin" prefix
            if ($request->routeIs('admin.*')) {
                return $next($request);
            } else {
                return response()->json(['error' => 'Unable to proceed action. Please check your token!'], 401);
            }
        } else {
            // If the user is not an admin, allow access to all routes with the "user" prefix
            if ($request->routeIs('user.*')) {
                return $next($request);
            } else {
                return response()->json(['error' => 'Unable to proceed action. Please check your token!'], 401);
            }
        }

    }
}
