<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(), 'number' => 'ORD-'.Str::upper(Str::random(12)),
            'customer_id' => User::factory(), 'status' => 'pending_payment', 'payment_status' => 'unpaid',
            'shipping_address' => ['recipient_name' => fake()->name(), 'phone' => '01700000000', 'line_one' => fake()->streetAddress(), 'city' => 'Dhaka', 'district' => 'Dhaka'],
            'subtotal' => 850, 'shipping_total' => 80, 'grand_total' => 930, 'payable_now' => 930, 'placed_at' => now(),
        ];
    }
}
