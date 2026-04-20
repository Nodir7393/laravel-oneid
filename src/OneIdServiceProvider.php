<?php

namespace Nodir\OneId;

use Illuminate\Support\ServiceProvider;
use Nodir\OneId\Http\Middleware\JwtAuth;
use Nodir\OneId\Http\Middleware\CheckRole;
use Nodir\OneId\Http\Middleware\CheckPermission;
use Nodir\OneId\Services\JwtService;
use Nodir\OneId\Services\OneIdService;

class OneIdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/oneid.php', 'oneid');

        $this->app->singleton(OneIdService::class);
        $this->app->singleton(JwtService::class);
    }

    public function boot(): void
    {
        // Config
        $this->publishes([
            __DIR__ . '/../config/oneid.php' => config_path('oneid.php'),
        ], 'oneid-config');

        // Migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'oneid-migrations');

        // Routes
        if (config('oneid.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/oneid.php');
        }

        // Middleware aliases
        $router = $this->app['router'];
        $router->aliasMiddleware('jwt.auth', JwtAuth::class);
        $router->aliasMiddleware('role', CheckRole::class);
        $router->aliasMiddleware('permission', CheckPermission::class);
    }
}
