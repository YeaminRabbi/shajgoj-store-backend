<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(4, true);

        return ['category_id' => Category::factory(), 'name' => ucfirst($name), 'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)), 'sku' => 'SKU-'.Str::upper(Str::random(10)), 'type' => 'stock', 'status' => 'published', 'price' => fake()->numberBetween(100, 10000), 'weight_kg' => 0.5, 'moq' => 1];
    }
}
