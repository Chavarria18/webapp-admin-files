<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CognitoService;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

use Illuminate\Pagination\Paginator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
          $this->app->singleton(CognitoService::class, function ($app) {

            $client = new CognitoIdentityProviderClient([
                'version' => 'latest',
                'region' => config('cognito.region'),
            ]);

            return new CognitoService(
                client: $client,
                clientId: config('cognito.client_id'),
                clientSecret: config('cognito.client_secret'),
                userPoolId: config('cognito.user_pool_id'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 
        Paginator::useBootstrapFive();
    }
}
