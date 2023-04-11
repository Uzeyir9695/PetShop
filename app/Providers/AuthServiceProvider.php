<?php

namespace App\Providers;

use Illuminate\Auth\RequestGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Auth::extend('jwt', function ($app, $name, array $config) {
            $guard = new RequestGuard(function ($request) use ($config) {
                $token = $request->bearerToken();
                if(!$token) {
                    print_r('Please provide a token');
                    exit();
                }
                $user = authUser($token);
                return $user;

            }, $app['request'], $app['auth']->createUserProvider($config['provider']));

            return $guard;
        });
    }
}
