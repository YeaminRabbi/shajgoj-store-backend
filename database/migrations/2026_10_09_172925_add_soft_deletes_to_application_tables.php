<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $tables = ['users', 'brands', 'categories', 'products', 'product_variants', 'orders', 'shipping_methods', 'delivery_zones'];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->softDeletes()->index();
            });
        }
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->boolean('deleted_with_product')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropColumn('deleted_with_product');
        });
        foreach (array_reverse($this->tables) as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->dropIndex(['deleted_at']);
                $table->dropSoftDeletes();
            });
        }
    }
};
