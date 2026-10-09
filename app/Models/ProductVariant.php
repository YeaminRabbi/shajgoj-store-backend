<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['product_id', 'name', 'sku', 'price', 'stock', 'reserved_stock', 'options', 'is_active'])]
class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $attributes = ['stock' => 0, 'reserved_stock' => 0, 'is_active' => true, 'deleted_with_product' => false];

    protected function casts(): array
    {
        return ['deleted_with_product' => 'boolean', 'options' => 'array', 'is_active' => 'boolean', 'price' => 'decimal:2', 'stock' => 'integer', 'reserved_stock' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function availableStock(): int
    {
        return max(0, $this->stock - $this->reserved_stock);
    }
}
