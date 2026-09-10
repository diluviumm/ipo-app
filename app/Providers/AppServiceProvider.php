<?php

namespace App\Providers;

use App\Auth\IpoUserProvider;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('ipo-eloquent', function (Application $app, array $config) {
            return new IpoUserProvider($app['hash'], $config['model']);
        });
        Paginator::defaultView('vendor.pagination.ipo');
    }
}
