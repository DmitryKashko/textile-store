<?php

namespace App\Repositories\Contracts;

use App\Data\ProductFilter;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepository
{
    public function paginate(ProductFilter $filter): LengthAwarePaginator;

    public function findActiveBySlug(string $slug): ?Product;

    /**
     * Locks the given products until the end of the current transaction.
     *
     * @param  list<int>  $ids
     * @return Collection<int, Product> keyed by id
     */
    public function lockForUpdate(array $ids): Collection;

    public function decrementStock(Product $product, int $quantity): void;
}
