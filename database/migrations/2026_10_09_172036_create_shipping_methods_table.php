<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('code')->unique();
            $table->string('type')->index();
            $table->decimal('rate_per_kg', 14, 2)->default(0);
            $table->decimal('minimum_charge', 14, 2)->default(0);
            $table->decimal('minimum_weight_kg', 10, 3)->default(0);
            $table->decimal('maximum_weight_kg', 10, 3)->nullable();
            $table->unsignedInteger('eta_min_days')->default(1);
            $table->unsignedInteger('eta_max_days')->default(7);
            $table->decimal('deposit_percentage', 5, 2)->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
