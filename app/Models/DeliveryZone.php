<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['shipping_method_id', 'name', 'districts', 'charge', 'cod_enabled', 'is_active'])]
class DeliveryZone extends Model
{
    use HasFactory, SoftDeletes;

    protected $attributes = ['cod_enabled' => false, 'is_active' => true];

    protected function casts(): array
    {
        return ['districts' => 'array', 'charge' => 'decimal:2', 'cod_enabled' => 'boolean', 'is_active' => 'boolean'];
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class)->withTrashed();
    }
}
