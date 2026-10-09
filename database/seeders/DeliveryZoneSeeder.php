<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Air Freight', 'এয়ার ফ্রেইট', 'air', 1150, 500, 10, 15, 70], ['Sea Freight', 'সি ফ্রেইট', 'sea', 480, 1000, 35, 45, 30],
            ['Cargo Freight', 'কার্গো ফ্রেইট', 'cargo', 730, 750, 18, 25, 50], ['Local Delivery', 'লোকাল ডেলিভারি', 'local', 0, 0, 1, 5, 100],
        ] as [$name, $nameBn, $code, $rate, $minimum, $minDays, $maxDays, $deposit]) {
            ShippingMethod::withTrashed()->updateOrCreate(['code' => $code], ['name' => $name, 'name_bn' => $nameBn, 'type' => $code, 'rate_per_kg' => $rate, 'minimum_charge' => $minimum, 'eta_min_days' => $minDays, 'eta_max_days' => $maxDays, 'deposit_percentage' => $deposit, 'is_active' => true]);
        }
        $local = ShippingMethod::withTrashed()->where('code', 'local')->firstOrFail();
        DeliveryZone::withTrashed()->updateOrCreate(['name' => 'Dhaka Metro'], ['shipping_method_id' => $local->id, 'districts' => ['Dhaka'], 'charge' => 80, 'cod_enabled' => true, 'is_active' => true]);
        DeliveryZone::withTrashed()->updateOrCreate(['name' => 'Nationwide'], ['shipping_method_id' => $local->id, 'districts' => ['Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barishal', 'Rangpur', 'Mymensingh'], 'charge' => 150, 'cod_enabled' => false, 'is_active' => true]);
    }
}
