<?php

namespace App\Services\Catalog;

use App\Data\ProductFilter;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CatalogService
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly CatalogCache $cache,
    ) {
    }

    public function search(ProductFilter $filter): LengthAwarePaginator
    {
        return $this->cache->remember(
            'products:'.$filter->cacheKey(),
            fn () => $this->products->paginate($filter),
        );
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->products->findActiveBySlug($slug);
    }
}
