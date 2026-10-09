<?php

namespace App\Models;

use App\Models\Concerns\ChecksDeletionDependencies;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category_id', 'brand_id', 'name', 'name_bn', 'slug', 'description', 'description_bn', 'type', 'status', 'sku', 'price', 'compare_price', 'weight_kg', 'moq', 'tax_rate_override', 'primary_image', 'gallery', 'attributes', 'tier_prices', 'seo', 'featured', 'published_at'])]
class Product extends Model
{
    use ChecksDeletionDependencies, HasFactory;
    use SoftDeletes {
        restore as private restoreSoftDeleted;
    }

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

        static::softDeleted(function (Product $product): void {
            $product->variants()->each(function (ProductVariant $variant): void {
                $variant->forceFill(['deleted_with_product' => true])->saveQuietly();
                $variant->delete();
            });
        });

        static::restored(function (Product $product): void {
            $product->variants()->onlyTrashed()->where('deleted_with_product', true)->each(function (ProductVariant $variant): void {
                $variant->forceFill(['deleted_with_product' => false])->saveQuietly();
                $variant->restore();
            });
        });
    }

    public function delete(): ?bool
    {
        return $this->getConnection()->transaction(fn (): ?bool => parent::delete());
    }

    public function restore(): bool
    {
        return $this->getConnection()->transaction(fn (): bool => $this->restoreSoftDeleted());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class)->withTrashed();
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
