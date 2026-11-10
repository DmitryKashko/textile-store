<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Dress fabrics', 'Shirt fabrics', 'Suit fabrics', 'Upholstery', 'Lining', 'Knitwear', 'Bedding',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
        ];
    }
}
