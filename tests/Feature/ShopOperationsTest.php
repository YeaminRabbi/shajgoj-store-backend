<?php

use App\Filament\Resources\DeliveryZones\Pages\ManageDeliveryZones;
use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;
use Database\Seeders\DeliveryZoneSeeder;
use Database\Seeders\RolePermissionSeeder;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('renders shop operation pages for admins', function (string $path) {
    $this->get($path)->assertSuccessful();
})->with(['/admin/orders', '/admin/delivery-zones']);

it('denies ordinary users access to shop operations', function (string $path) {
    $this->actingAs(User::factory()->create());
    $this->get($path)->assertForbidden();
})->with(['/admin/orders', '/admin/delivery-zones']);

it('redirects guests to login', function (string $path) {
    auth()->logout();
    $this->get($path)->assertRedirect('/admin/login');
})->with(['/admin/orders', '/admin/delivery-zones']);

it('seeds shipping methods and delivery zones without duplicates', function () {
    $this->seed(DeliveryZoneSeeder::class);
    $this->seed(DeliveryZoneSeeder::class);
    expect(ShippingMethod::count())->toBe(4)->and(DeliveryZone::count())->toBe(2);
    $zone = DeliveryZone::where('name', 'Dhaka Metro')->firstOrFail();
    expect($zone->districts)->toBe(['Dhaka'])->and($zone->charge)->toBe('80.00')
        ->and($zone->cod_enabled)->toBeTrue()->and($zone->shippingMethod->code)->toBe('local');
});

it('creates edits and deletes delivery zones', function () {
    $method = ShippingMethod::factory()->create();
    Livewire::test(ManageDeliveryZones::class)->callAction(CreateAction::class, data: [
        'name' => 'Dhaka', 'shipping_method_id' => $method->id, 'districts' => ['Dhaka'],
        'charge' => 80, 'cod_enabled' => true, 'is_active' => true,
    ])->assertHasNoActionErrors();
    $zone = DeliveryZone::where('name', 'Dhaka')->firstOrFail();
    Livewire::test(ManageDeliveryZones::class)->callTableAction(EditAction::class, $zone, data: ['charge' => 100, 'districts' => ['Dhaka', 'Gazipur']])->assertHasNoActionErrors();
    expect($zone->fresh()->charge)->toBe('100.00')->and($zone->fresh()->districts)->toBe(['Dhaka', 'Gazipur']);
    Livewire::test(ManageDeliveryZones::class)->callTableAction(DeleteAction::class, $zone);
    $this->assertSoftDeleted($zone);
});

it('validates delivery zones', function (array $data, string $field) {
    Livewire::test(ManageDeliveryZones::class)->callAction(CreateAction::class, data: [
        'name' => 'Test Zone', 'districts' => ['Dhaka'], 'charge' => 80, 'is_active' => true, ...$data,
    ])->assertHasActionErrors([$field]);
    expect(DeliveryZone::count())->toBe(0);
})->with([
    'negative charge' => [['charge' => -1], 'charge'],
    'missing districts' => [['districts' => []], 'districts'],
    'unknown shipping method' => [['shipping_method_id' => 99999], 'shipping_method_id'],
]);

it('lists existing orders with customer details', function () {
    $order = Order::factory()->create(['number' => 'ORDER-1001', 'grand_total' => 950]);
    Livewire::test(ManageOrders::class)->assertCanSeeTableRecords([$order])->assertSee('ORDER-1001')->assertSee($order->customer->email);
});

it('updates order statuses without changing order identity or totals', function () {
    $order = Order::factory()->create(['grand_total' => 950, 'payable_now' => 950]);
    Livewire::test(ManageOrders::class)->callTableAction(EditAction::class, $order, data: ['status' => 'processing', 'payment_status' => 'paid', 'number' => 'TAMPERED', 'grand_total' => 1])->assertHasNoActionErrors();
    expect($order->fresh()->status)->toBe('processing')->and($order->fresh()->payment_status)->toBe('paid')
        ->and($order->fresh()->number)->toBe($order->number)->and($order->fresh()->grand_total)->toBe('950.00');
});

it('rejects unsupported order statuses', function () {
    $order = Order::factory()->create();
    Livewire::test(ManageOrders::class)->callTableAction(EditAction::class, $order, data: ['status' => 'invalid', 'payment_status' => 'invalid'])->assertHasActionErrors(['status', 'payment_status']);
    expect($order->fresh()->status)->toBe('pending_payment');
});

it('does not offer incomplete manual order creation', function () {
    Livewire::test(ManageOrders::class)->assertActionDoesNotExist(CreateAction::class);
});

it('separates order permissions from catalog and delivery zone permissions', function () {
    Role::findOrCreate('order-manager', 'web')->givePermissionTo('manage orders');
    $user = User::factory()->create();
    $user->assignRole('order-manager');
    $this->actingAs($user);
    $this->get('/admin/orders')->assertSuccessful();
    $this->get('/admin/products')->assertForbidden();
    $this->get('/admin/delivery-zones')->assertForbidden();
});

it('retains delivery zones after deleting a shipping method', function () {
    $method = ShippingMethod::factory()->create();
    $zone = DeliveryZone::factory()->create(['shipping_method_id' => $method->id]);
    $method->delete();
    expect($zone->fresh()->shipping_method_id)->toBe($method->id)->and($zone->fresh()->shippingMethod->trashed())->toBeTrue();
});

it('deletes an order without deleting its customer', function () {
    $order = Order::factory()->create();
    $customer = $order->customer;
    Livewire::test(ManageOrders::class)->callTableAction(DeleteAction::class, $order);
    $this->assertSoftDeleted($order);
    $this->assertModelExists($customer);
});

it('filters shipping methods by their active dates', function () {
    $active = ShippingMethod::factory()->create();
    ShippingMethod::factory()->create(['is_active' => false]);
    ShippingMethod::factory()->create(['starts_at' => now()->addDay()]);
    ShippingMethod::factory()->create(['ends_at' => now()->subDay()]);
    expect(ShippingMethod::currentlyActive()->pluck('id')->all())->toBe([$active->id]);
});

it('rolls back and reapplies shop migrations without removing the catalog', function () {
    $this->artisan('migrate:rollback', ['--step' => 5])->assertSuccessful();
    expect(Schema::hasTable('orders'))->toBeFalse()->and(Schema::hasTable('delivery_zones'))->toBeFalse()
        ->and(Schema::hasTable('shipping_methods'))->toBeFalse()->and(Schema::hasTable('products'))->toBeTrue();
    $this->artisan('migrate')->assertSuccessful();
    $this->assertModelExists(Order::factory()->create());
    $this->assertModelExists(DeliveryZone::factory()->create());
});
