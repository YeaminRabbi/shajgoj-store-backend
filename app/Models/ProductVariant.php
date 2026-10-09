<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'name', 'sku', 'price', 'stock', 'reserved_stock', 'options', 'is_active'])]
class ProductVariant extends Model
{
    use HasFactory;

    protected $attributes = ['stock' => 0, 'reserved_stock' => 0, 'is_active' => true];

    protected function casts(): array
    {
        return ['options' => 'array', 'is_active' => 'boolean', 'price' => 'decimal:2', 'stock' => 'integer', 'reserved_stock' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function availableStock(): int
    {
        return max(0, $this->stock - $this->reserved_stock);
    }
}
