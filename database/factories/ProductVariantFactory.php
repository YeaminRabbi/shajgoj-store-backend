<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ProductVariant> */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return ['product_id' => Product::factory(), 'name' => 'Standard', 'sku' => 'VAR-'.Str::upper(Str::random(10)), 'stock' => 10, 'reserved_stock' => 0, 'is_active' => true];
    }
}
