<?php

namespace Tests\Feature\Catalog;

use App\Enums\Material;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_active_products(): void
    {
        Product::factory()->count(2)->create();
        Product::factory()->inactive()->create();

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug', 'material', 'color', 'width_cm', 'price', 'stock', 'category']],
                'meta' => ['current_page', 'total'],
            ]);
    }

    public function test_it_filters_by_category_material_and_price(): void
    {
        $category = Category::factory()->create();
        $match = Product::factory()->for($category)->create(['material' => Material::Linen, 'price' => 15000]);
        Product::factory()->for($category)->create(['material' => Material::Silk, 'price' => 15000]);
        Product::factory()->for($category)->create(['material' => Material::Linen, 'price' => 50000]);
        Product::factory()->create(['material' => Material::Linen, 'price' => 15000]);

        $this->getJson("/api/products?category_id={$category->id}&material=linen&price_to=20000")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $match->id);
    }

    public function test_it_sorts_by_price(): void
    {
        $category = Category::factory()->create();
        $cheap = Product::factory()->for($category)->create(['price' => 5000]);
        $expensive = Product::factory()->for($category)->create(['price' => 9000]);

        $this->getJson('/api/products?sort=price_desc')
            ->assertOk()
            ->assertJsonPath('data.0.id', $expensive->id)
            ->assertJsonPath('data.1.id', $cheap->id);
    }

    public function test_it_rejects_invalid_filters(): void
    {
        $this->getJson('/api/products?material=leather&price_from=500&price_to=100')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['material', 'price_to']);
    }

    public function test_listing_is_cached_and_refreshed_when_a_product_changes(): void
    {
        $product = Product::factory()->create(['price' => 10000]);

        $this->getJson('/api/products')->assertJsonPath('data.0.price', 10000);

        // bypass model events: cached listing must stay as is
        Product::query()->whereKey($product->id)->update(['price' => 12000]);
        $this->getJson('/api/products')->assertJsonPath('data.0.price', 10000);

        // a regular update goes through the observer and invalidates the cache
        $product->update(['price' => 13000]);
        $this->getJson('/api/products')->assertJsonPath('data.0.price', 13000);
    }

    public function test_it_shows_a_product_by_slug(): void
    {
        $product = Product::factory()->create();

        $this->getJson("/api/products/{$product->slug}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.category.id', $product->category_id);
    }

    public function test_inactive_product_is_not_found(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->getJson("/api/products/{$product->slug}")->assertNotFound();
    }
}
