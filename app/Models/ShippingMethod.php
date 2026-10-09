<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'name_bn', 'code', 'type', 'rate_per_kg', 'minimum_charge', 'minimum_weight_kg', 'maximum_weight_kg', 'eta_min_days', 'eta_max_days', 'deposit_percentage', 'is_active', 'starts_at', 'ends_at'])]
class ShippingMethod extends Model
{
    use HasFactory;

    protected $attributes = ['rate_per_kg' => 0, 'minimum_charge' => 0, 'minimum_weight_kg' => 0, 'eta_min_days' => 1, 'eta_max_days' => 7, 'deposit_percentage' => 100, 'is_active' => true];

    protected function casts(): array
    {
        return ['rate_per_kg' => 'decimal:2', 'minimum_charge' => 'decimal:2', 'minimum_weight_kg' => 'decimal:3', 'maximum_weight_kg' => 'decimal:3', 'eta_min_days' => 'integer', 'eta_max_days' => 'integer', 'deposit_percentage' => 'decimal:2', 'is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where(fn (Builder $q): Builder => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))->where(fn (Builder $q): Builder => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
