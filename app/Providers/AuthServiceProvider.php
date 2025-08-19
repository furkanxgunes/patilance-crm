<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Providers\CustomUserProvider;
use Illuminate\Support\Facades\Validator;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register custom user provider
        Auth::provider('custom', function ($app, array $config) {
            return new CustomUserProvider($app['hash'], $config['model']);
        });

        // Add custom validation rule for email or username
        Validator::extend('email_or_username', function ($attribute, $value, $parameters, $validator) {
            return filter_var($value, FILTER_VALIDATE_EMAIL) || !preg_match('/[^a-z0-9_.]/i', $value);
        });
    }
}
