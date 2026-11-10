<?php

namespace App\Data;

use App\Enums\Material;

final readonly class ProductFilter
{
    public function __construct(
        public ?int $categoryId = null,
        public ?Material $material = null,
        public ?string $color = null,
        public ?int $priceFrom = null,
        public ?int $priceTo = null,
        public string $sort = 'newest',
        public int $perPage = 20,
        public int $page = 1,
    ) {
    }

    public static function fromArray(array $input): self
    {
        return new self(
            categoryId: isset($input['category_id']) ? (int) $input['category_id'] : null,
            material: isset($input['material']) ? Material::from($input['material']) : null,
            color: $input['color'] ?? null,
            priceFrom: isset($input['price_from']) ? (int) $input['price_from'] : null,
            priceTo: isset($input['price_to']) ? (int) $input['price_to'] : null,
            sort: $input['sort'] ?? 'newest',
            perPage: (int) ($input['per_page'] ?? 20),
            page: (int) ($input['page'] ?? 1),
        );
    }

    public function cacheKey(): string
    {
        return md5(serialize([
            $this->categoryId,
            $this->material?->value,
            $this->color,
            $this->priceFrom,
            $this->priceTo,
            $this->sort,
            $this->perPage,
            $this->page,
        ]));
    }
}
