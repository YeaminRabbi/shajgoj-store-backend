<?php

namespace Database\Factories;

use App\Models\DeliveryZone;
use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DeliveryZone> */
class DeliveryZoneFactory extends Factory
{
    public function definition(): array
    {
        return ['shipping_method_id' => ShippingMethod::factory(), 'name' => fake()->city(), 'districts' => ['Dhaka'], 'charge' => 80, 'cod_enabled' => true, 'is_active' => true];
    }
}
