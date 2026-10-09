<?php

use App\Filament\Resources\Brands\Pages\ManageBrands;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CosmeticsCatalogSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StaticCatalogSeeder;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('seeds both catalogs repeatedly without merchant dependencies or duplicates', function () {
    $this->seed([StaticCatalogSeeder::class, CosmeticsCatalogSeeder::class]);
    $this->seed([StaticCatalogSeeder::class, CosmeticsCatalogSeeder::class]);
    expect(Category::count())->toBe(15)->and(Brand::count())->toBe(2)
        ->and(Product::count())->toBe(27)->and(ProductVariant::count())->toBe(27);
    $product = Product::where('sku', 'COS-0001')->firstOrFail();
    expect($product->category->slug)->toBe('skin-care')->and($product->brand->slug)->toBe('cosmetic-essentials')
        ->and($product->variants->first()->availableStock())->toBe(60);
});

it('renders the catalog pages for an administrator', function (string $path) {
    $this->get($path)->assertSuccessful();
})->with(['/admin/products', '/admin/products/create', '/admin/categories', '/admin/brands']);

it('denies catalog access to ordinary users', function (string $path) {
    $this->actingAs(User::factory()->create());
    $this->get($path)->assertForbidden();
})->with(['/admin/products', '/admin/categories', '/admin/brands']);

it('creates edits and deletes catalog records through Filament', function (string $page, string $model, array $data) {
    Livewire::test($page)->callAction(CreateAction::class, data: $data)->assertHasNoActionErrors();
    $record = $model::where('slug', $data['slug'])->firstOrFail();
    Livewire::test($page)->callTableAction(EditAction::class, $record, data: ['name' => 'Updated Name'])->assertHasNoActionErrors();
    expect($record->fresh()->name)->toBe('Updated Name');
    Livewire::test($page)->callTableAction(DeleteAction::class, $record);
    $this->assertModelMissing($record);
})->with([
    'category' => [ManageCategories::class, Category::class, ['name' => 'Skin Care', 'slug' => 'skin-care', 'sort_order' => 0, 'is_active' => true]],
    'brand' => [ManageBrands::class, Brand::class, ['name' => 'Beauty Brand', 'slug' => 'beauty-brand', 'status' => 'approved']],
]);

it('creates a product with stock variants and an uploaded image', function () {
    Storage::fake('public');
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();
    Livewire::test(CreateProduct::class)->fillForm([
        'category_id' => $category->id, 'brand_id' => $brand->id,
        'name' => 'Serum', 'slug' => 'serum', 'sku' => 'SERUM-01',
        'type' => 'stock', 'status' => 'published', 'price' => 850, 'weight_kg' => 0.25, 'moq' => 1,
        'primary_image' => UploadedFile::fake()->image('serum.jpg'),
        'variants' => [['name' => '30 ml', 'sku' => 'SERUM-30', 'price' => 850, 'stock' => 10, 'is_active' => true]],
    ])->call('create')->assertHasNoFormErrors();
    $product = Product::where('sku', 'SERUM-01')->firstOrFail();
    Storage::disk('public')->assertExists($product->primary_image);
    expect($product->variants->first()->availableStock())->toBe(10)->and($product->published_at)->not->toBeNull();
});

it('preserves seeded image urls when editing and deletes variants with the product', function () {
    $this->seed(StaticCatalogSeeder::class);
    $product = Product::firstOrFail();
    $image = $product->primary_image;
    $gallery = $product->gallery;
    $variant = $product->variants->first();
    Livewire::test(EditProduct::class, ['record' => $product->id])->fillForm(['name' => 'Updated Product'])->call('save')->assertHasNoFormErrors();
    expect($product->fresh()->name)->toBe('Updated Product')->and($product->fresh()->primary_image)->toBe($image)->and($product->fresh()->gallery)->toBe($gallery);
    Livewire::test(EditProduct::class, ['record' => $product->id])->callAction(DeleteAction::class);
    $this->assertModelMissing($product);
    $this->assertModelMissing($variant);
});

it('rejects negative product prices', function () {
    $category = Category::factory()->create();
    Livewire::test(CreateProduct::class)->fillForm([
        'category_id' => $category->id, 'name' => 'New', 'slug' => 'new', 'sku' => 'NEW-01',
        'type' => 'stock', 'status' => 'draft', 'price' => -1, 'weight_kg' => 0.5, 'moq' => 1,
    ])->call('create')->assertHasFormErrors(['price' => 'min']);
    expect(Product::where('sku', 'NEW-01')->exists())->toBeFalse();
});

it('prevents assigning a category as its own parent', function () {
    $category = Category::factory()->create();
    Livewire::test(ManageCategories::class)->callTableAction(EditAction::class, $category, data: ['parent_id' => $category->id])->assertHasActionErrors(['parent_id']);
    expect($category->fresh()->parent_id)->toBeNull();
});

it('keeps categories with products protected from deletion', function () {
    $product = Product::factory()->create();
    Livewire::test(ManageCategories::class)->assertTableActionHidden(DeleteAction::class, $product->category);
    $this->assertModelExists($product->category);
});

it('rejects duplicate variant skus without saving a product', function () {
    $existing = Product::factory()->create();
    $existing->variants()->create(['name' => 'Existing', 'sku' => 'DUPLICATE', 'stock' => 1]);
    Livewire::test(CreateProduct::class)->fillForm([
        'category_id' => $existing->category_id, 'name' => 'New', 'slug' => 'new', 'sku' => 'NEW-01',
        'type' => 'stock', 'status' => 'draft', 'price' => 100, 'weight_kg' => 0.5, 'moq' => 1,
        'variants' => [['name' => 'Duplicate', 'sku' => 'DUPLICATE', 'stock' => 0, 'is_active' => true]],
    ])->call('create')->assertHasFormErrors();
    expect(Product::where('sku', 'NEW-01')->exists())->toBeFalse();
});

it('rejects invalid catalog quantities', function (array $data, string $field) {
    $category = Category::factory()->create();
    Livewire::test(CreateProduct::class)->fillForm([
        'category_id' => $category->id, 'name' => 'New', 'slug' => 'new', 'sku' => 'NEW-01',
        'type' => 'stock', 'status' => 'draft', 'price' => 100, 'weight_kg' => 0.5, 'moq' => 1,
        ...$data,
    ])->call('create')->assertHasFormErrors([$field]);
    expect(Product::count())->toBe(0);
})->with([
    'zero minimum order' => [['moq' => 0], 'moq'],
    'fractional minimum order' => [['moq' => 1.5], 'moq'],
    'negative weight' => [['weight_kg' => -1], 'weight_kg'],
    'excess tax' => [['tax_rate_override' => 101], 'tax_rate_override'],
]);

it('preserves stock reservations when editing a variant', function () {
    $product = Product::factory()->create();
    $variant = $product->variants()->create(['name' => 'Reserved', 'sku' => 'RESERVED', 'stock' => 10, 'reserved_stock' => 5]);
    $component = Livewire::test(EditProduct::class, ['record' => $product->id]);
    $key = array_key_first($component->get('data.variants'));
    $component->set("data.variants.$key.stock", 4)->call('save')->assertHasFormErrors(["variants.$key.stock"]);
    expect($variant->fresh()->stock)->toBe(10)->and($variant->fresh()->availableStock())->toBe(5);
});

it('protects populated categories during bulk deletion', function () {
    $product = Product::factory()->create();
    $empty = Category::factory()->create();
    Livewire::test(ManageCategories::class)->callTableBulkAction(DeleteBulkAction::class, [$product->category, $empty]);
    $this->assertModelExists($product->category);
    $this->assertModelMissing($empty);
});

it('prevents moving a category beneath a descendant', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);
    Livewire::test(ManageCategories::class)->callTableAction(EditAction::class, $parent, data: ['parent_id' => $child->id])->assertHasActionErrors(['parent_id']);
    expect($parent->fresh()->parent_id)->toBeNull();
});

it('allows deleting a brand without deleting its products', function () {
    $brand = Brand::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id]);
    Livewire::test(ManageBrands::class)->callTableAction(DeleteAction::class, $brand);
    $this->assertModelMissing($brand);
    expect($product->fresh()->brand_id)->toBeNull();
});
