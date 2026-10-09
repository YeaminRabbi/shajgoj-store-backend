<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('description_bn')->nullable();
            $table->string('type')->default('stock')->index();
            $table->string('status')->default('draft')->index();
            $table->string('sku')->unique();
            $table->decimal('price', 14, 2);
            $table->decimal('compare_price', 14, 2)->nullable();
            $table->decimal('weight_kg', 10, 3)->default(0.5);
            $table->unsignedInteger('moq')->default(1);
            $table->decimal('tax_rate_override', 5, 2)->nullable();
            $table->string('primary_image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('attributes')->nullable();
            $table->json('tier_prices')->nullable();
            $table->json('seo')->nullable();
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
