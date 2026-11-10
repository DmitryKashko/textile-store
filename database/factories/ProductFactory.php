<?php

namespace Database\Factories;

use App\Enums\Material;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $material = fake()->randomElement(Material::cases());
        $color = fake()->randomElement(['white', 'black', 'beige', 'navy', 'grey', 'olive', 'red']);
        $name = ucfirst($color).' '.$material->value.' '.fake()->randomElement(['satin', 'twill', 'poplin', 'jersey', 'canvas']);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'description' => fake()->sentence(12),
            'material' => $material,
            'color' => $color,
            'width_cm' => fake()->randomElement([140, 150, 160, 220, 280]),
            'price' => fake()->numberBetween(30, 400) * 100,
            'stock' => fake()->numberBetween(0, 500),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
