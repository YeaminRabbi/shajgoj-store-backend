<?php

use App\Exceptions\DeletionBlockedException;
use App\Filament\Resources\Brands\Pages\ManageBrands;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\SoftDeleteProbe;
use App\Models\User;
use Database\Seeders\DeliveryZoneSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StaticCatalogSeeder;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('soft deletes and restores every application model', function (string $model) {
    $record = $model::factory()->create();
    $record->delete();
    $this->assertSoftDeleted($record);
    expect($model::find($record->id))->toBeNull();
    $archived = $model::onlyTrashed()->findOrFail($record->id);
    $archived->restore();
    $this->assertNotSoftDeleted($record);
    expect($model::find($record->id))->not->toBeNull();
})->with([User::class, Brand::class, Category::class, Product::class, ProductVariant::class, Order::class, ShippingMethod::class, DeliveryZone::class]);

it('shows a warning when deleting a brand with linked products', function () {
    $brand = Brand::factory()->create();
    Product::factory()->create(['brand_id' => $brand->id]);
    Livewire::test(ManageBrands::class)->assertTableActionVisible(DeleteAction::class, $brand)
        ->callTableAction(DeleteAction::class, $brand)
        ->assertNotified(Notification::make()->title('Deletion blocked')->warning()->body('Cannot delete this brand because it has 1 linked product. Remove or reassign the linked records first.'));
    $this->assertNotSoftDeleted($brand);
});

it('blocks deletion when a linked product is archived', function () {
    $brand = Brand::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id]);
    $product->delete();
    expect(fn () => $brand->delete())->toThrow(DeletionBlockedException::class);
    $this->assertNotSoftDeleted($brand);
});

it('shows a warning when deleting a category with children or products', function (string $dependency) {
    $category = Category::factory()->create();
    if ($dependency === 'child') {
        Category::factory()->create(['parent_id' => $category->id]);
    } else {
        Product::factory()->create(['category_id' => $category->id]);
    }
    Livewire::test(ManageCategories::class)->callTableAction(DeleteAction::class, $category)->assertNotified('Deletion blocked');
    $this->assertNotSoftDeleted($category);
})->with(['child', 'product']);

it('blocks direct deletion of a category with archived children', function () {
    $category = Category::factory()->create();
    Category::factory()->create(['parent_id' => $category->id])->delete();
    expect(fn () => $category->delete())->toThrow(DeletionBlockedException::class);
    $this->assertNotSoftDeleted($category);
});

it('soft deletes product variants together and restores only the variants deleted with the product', function () {
    $product = Product::factory()->create();
    $active = ProductVariant::factory()->create(['product_id' => $product->id]);
    $archived = ProductVariant::factory()->create(['product_id' => $product->id]);
    $archived->delete();
    Livewire::test(EditProduct::class, ['record' => $product->id])->callAction(DeleteAction::class);
    $this->assertSoftDeleted($product);
    $this->assertSoftDeleted($active);
    Livewire::test(EditProduct::class, ['record' => $product->id])->callAction(RestoreAction::class);
    $this->assertNotSoftDeleted($product);
    $this->assertNotSoftDeleted($active);
    $this->assertSoftDeleted($archived);
});

it('blocks product deletion when another table references it', function () {
    Schema::create('product_references', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('product_id')->constrained();
        $table->softDeletes();
    });
    try {
        $product = Product::factory()->create();
        $product->getConnection()->table('product_references')->insert(['product_id' => $product->id, 'deleted_at' => now()]);
        Livewire::test(EditProduct::class, ['record' => $product->id])->callAction(DeleteAction::class)->assertNotified('Deletion blocked');
        $this->assertNotSoftDeleted($product);
        expect(fn () => $product->delete())->toThrow(DeletionBlockedException::class);
    } finally {
        Schema::dropIfExists('product_references');
    }
});

it('warns about blocked records in a mixed bulk deletion', function () {
    $brand = Brand::factory()->create();
    Product::factory()->create(['brand_id' => $brand->id]);
    $empty = Brand::factory()->create();
    Livewire::test(ManageBrands::class)->callTableBulkAction(DeleteBulkAction::class, [$brand, $empty])->assertNotified('Deletion blocked');
    $this->assertNotSoftDeleted($brand);
    $this->assertSoftDeleted($empty);
});

it('restores archived records from the trash filter', function () {
    $brand = Brand::factory()->create();
    $brand->delete();
    Livewire::test(ManageBrands::class)->filterTable('trashed', false)->assertCanSeeTableRecords([$brand])
        ->callTableAction(RestoreAction::class, $brand);
    $this->assertNotSoftDeleted($brand);
});

it('preserves historical customer and shipping method relationships', function () {
    $order = Order::factory()->create();
    $customer = $order->customer;
    $customer->delete();
    expect($order->fresh()->customer->trashed())->toBeTrue();
    $zone = DeliveryZone::factory()->create();
    $method = $zone->shippingMethod;
    $method->delete();
    expect($zone->fresh()->shippingMethod->trashed())->toBeTrue();
});

it('does not allow archived brands to be selected for new products', function () {
    $brand = Brand::factory()->create();
    $brand->delete();
    $category = Category::factory()->create();
    Livewire::test(CreateProduct::class)->fillForm([
        'name' => 'New Product', 'slug' => 'new-product', 'sku' => 'NEW-01', 'category_id' => $category->id,
        'brand_id' => $brand->id, 'price' => 100, 'weight_kg' => 0.5, 'moq' => 1,
        'type' => 'stock', 'status' => 'draft', 'variants' => [],
    ])->call('create')->assertHasFormErrors(['brand_id']);
    expect(Product::count())->toBe(0);
});

it('does not duplicate or revive archived catalog seed records', function () {
    $this->seed(StaticCatalogSeeder::class);
    $product = Product::firstOrFail();
    $product->delete();
    $this->seed(StaticCatalogSeeder::class);
    $this->assertSoftDeleted($product);
    expect(Product::withTrashed()->count())->toBe(12)->and(ProductVariant::withTrashed()->count())->toBe(12);
});

it('reseeds archived shipping methods and delivery zones without reviving them', function () {
    $this->seed(DeliveryZoneSeeder::class);
    $method = ShippingMethod::where('code', 'local')->firstOrFail();
    $zone = DeliveryZone::where('name', 'Dhaka Metro')->firstOrFail();
    $method->delete();
    $zone->delete();
    $this->seed(DeliveryZoneSeeder::class);
    $this->assertSoftDeleted($method);
    $this->assertSoftDeleted($zone);
    expect(ShippingMethod::withTrashed()->count())->toBe(4)->and(DeliveryZone::withTrashed()->count())->toBe(2);
});

it('warns immediately when opening deletion of a dependent record', function () {
    $brand = Brand::factory()->create();
    Product::factory()->create(['brand_id' => $brand->id]);
    Livewire::test(ManageBrands::class)->mountTableAction(DeleteAction::class, $brand)->assertNotified('Deletion blocked');
    $this->assertNotSoftDeleted($brand);
});

it('generates future models and migrations with working soft deletes by default', function () {
    $this->app->getNamespace();
    $originalAppPath = app_path();
    $probePath = storage_path('framework/testing/model-'.Str::uuid());
    File::ensureDirectoryExists($probePath.'/Models');
    $this->app->useAppPath($probePath);
    $migrationPath = null;
    $modelPath = $probePath.'/Models/SoftDeleteProbe.php';
    try {
        $this->artisan('make:model', ['name' => 'SoftDeleteProbe', '--migration' => true])->assertSuccessful();
        $migrationPath = glob(database_path('migrations/*_create_soft_delete_probes_table.php'))[0];
        $migration = require $migrationPath;
        $migration->up();
        require $modelPath;
        $record = SoftDeleteProbe::create();
        $record->delete();
        $this->assertSoftDeleted($record);
        expect(SoftDeleteProbe::count())->toBe(0);
        $record->restore();
        $this->assertNotSoftDeleted($record);
        $migration->down();
    } finally {
        $this->app->useAppPath($originalAppPath);
        File::delete($modelPath);
        if ($migrationPath !== null) {
            File::delete($migrationPath);
        }
        if (is_dir($probePath.'/Models')) {
            rmdir($probePath.'/Models');
        }
        rmdir($probePath);
    }
});
