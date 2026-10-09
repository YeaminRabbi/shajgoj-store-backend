<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'brand_id', 'name', 'name_bn', 'slug', 'description', 'description_bn', 'type', 'status', 'sku', 'price', 'compare_price', 'weight_kg', 'moq', 'tax_rate_override', 'primary_image', 'gallery', 'attributes', 'tier_prices', 'seo', 'featured', 'published_at'])]
class Product extends Model
{
    use HasFactory;

    protected $attributes = ['type' => 'stock', 'status' => 'draft', 'weight_kg' => 0.5, 'moq' => 1, 'featured' => false];

    protected function casts(): array
    {
        return [
            'gallery' => 'array', 'attributes' => 'array', 'tier_prices' => 'array', 'seo' => 'array',
            'featured' => 'boolean', 'published_at' => 'datetime', 'price' => 'decimal:2',
            'compare_price' => 'decimal:2', 'weight_kg' => 'decimal:3', 'tax_rate_override' => 'decimal:2', 'moq' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if ($product->status === 'published' && $product->published_at === null) {
                $product->published_at = now();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function isSourcing(): bool
    {
        return $this->type === 'sourcing';
    }
}
