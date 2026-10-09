<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('number')->unique();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('pending_payment')->index();
            $table->string('payment_method')->default('sslcommerz');
            $table->string('payment_status')->default('unpaid')->index();
            $table->string('currency', 3)->default('BDT');
            $table->json('shipping_address');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('shipping_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('payable_now', 14, 2)->default(0);
            $table->decimal('balance_due', 14, 2)->default(0);
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('payment_expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
