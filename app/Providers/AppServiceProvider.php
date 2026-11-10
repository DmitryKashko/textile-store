<?php

namespace App\Providers;

use App\Repositories\Contracts\OrderRepository;
use App\Repositories\Contracts\ProductRepository;
use App\Repositories\Eloquent\EloquentOrderRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        ProductRepository::class => EloquentProductRepository::class,
        OrderRepository::class => EloquentOrderRepository::class,
    ];

    public function boot(): void
    {
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('orders', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
    }
}
