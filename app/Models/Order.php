<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['uuid', 'number', 'customer_id', 'status', 'payment_method', 'payment_status', 'currency', 'shipping_address', 'subtotal', 'discount_total', 'tax_total', 'shipping_total', 'grand_total', 'payable_now', 'balance_due', 'placed_at', 'payment_expires_at'])]
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $attributes = ['status' => 'pending_payment', 'payment_method' => 'sslcommerz', 'payment_status' => 'unpaid', 'currency' => 'BDT', 'subtotal' => 0, 'discount_total' => 0, 'tax_total' => 0, 'shipping_total' => 0, 'grand_total' => 0, 'payable_now' => 0, 'balance_due' => 0];

    protected function casts(): array
    {
        return ['shipping_address' => 'array', 'placed_at' => 'datetime', 'payment_expires_at' => 'datetime', 'subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'tax_total' => 'decimal:2', 'shipping_total' => 'decimal:2', 'grand_total' => 'decimal:2', 'payable_now' => 'decimal:2', 'balance_due' => 'decimal:2'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id')->withTrashed();
    }
}
