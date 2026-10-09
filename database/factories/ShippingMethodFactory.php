<?php

namespace Database\Factories;

use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ShippingMethod> */
class ShippingMethodFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => 'Local Delivery', 'code' => 'local-'.Str::lower(Str::random(8)), 'type' => 'local', 'is_active' => true];
    }
}
