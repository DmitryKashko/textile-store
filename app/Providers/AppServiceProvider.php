<?php

namespace App\Providers;

use App\Repositories\Contracts\ProductRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        ProductRepository::class => EloquentProductRepository::class,
    ];

    public function boot(): void
    {
        //
    }
}
