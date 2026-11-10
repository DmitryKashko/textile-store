<?php

namespace App\Repositories\Eloquent;

use App\Data\ProductFilter;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentProductRepository implements ProductRepository
{
    public function paginate(ProductFilter $filter): LengthAwarePaginator
    {
        $query = Product::query()
            ->active()
            ->with('category')
            ->when($filter->categoryId, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filter->material, fn ($q, $material) => $q->where('material', $material))
            ->when($filter->color, fn ($q, $color) => $q->where('color', $color))
            ->when($filter->priceFrom !== null, fn ($q) => $q->where('price', '>=', $filter->priceFrom))
            ->when($filter->priceTo !== null, fn ($q) => $q->where('price', '<=', $filter->priceTo));

        match ($filter->sort) {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            default => $query->latest('id'),
        };

        return $query->paginate(perPage: $filter->perPage, page: $filter->page);
    }

    public function findActiveBySlug(string $slug): ?Product
    {
        return Product::query()->active()->with('category')->where('slug', $slug)->first();
    }

    public function lockForUpdate(array $ids): Collection
    {
        // stable lock order, so two orders with the same products can't deadlock
        return Product::query()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    public function decrementStock(Product $product, int $quantity): void
    {
        $product->decrement('stock', $quantity);
    }
}
