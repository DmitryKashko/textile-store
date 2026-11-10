<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\Catalog\CatalogCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ProductObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly CatalogCache $cache)
    {
    }

    public function saved(Product $product): void
    {
        $this->cache->flush();
    }

    public function deleted(Product $product): void
    {
        $this->cache->flush();
    }
}
