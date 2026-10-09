<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Filament\Notifications\DatabaseNotification;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->createQuietly();
    $this->admin->assignRole('admin');
    $this->catalogManager = User::factory()->createQuietly();
    $this->catalogManager->givePermissionTo('manage catalog');
    $this->orderManager = User::factory()->createQuietly();
    $this->orderManager->givePermissionTo('manage orders');
    $this->customer = User::factory()->createQuietly();
});

it('notifies admins of new registrations without exposing passwords', function () {
    Notification::fake();
    $user = User::factory()->create(['name' => '<script>Test</script>']);
    Notification::assertSentTo($this->admin, DatabaseNotification::class, fn ($notification): bool => $notification->data['title'] === 'New user registered'
        && str_contains($notification->data['body'], e($user->name))
        && ! str_contains(json_encode($notification->data), $user->password)
        && $notification->afterCommit === true);
    Notification::assertNotSentTo([$this->catalogManager, $this->orderManager, $this->customer, $user], DatabaseNotification::class);
});

it('notifies only catalog managers when a product is added', function () {
    Notification::fake();
    $product = Product::factory()->create();
    Notification::assertSentTo([$this->admin, $this->catalogManager], DatabaseNotification::class, fn ($notification): bool => $notification->data['title'] === 'New product added' && str_contains($notification->data['body'], $product->name));
    Notification::assertNotSentTo([$this->orderManager, $this->customer], DatabaseNotification::class);
    Notification::assertCount(2);
});

it('notifies order managers when an order is placed', function () {
    Notification::fake();
    $order = Order::factory()->for($this->customer, 'customer')->create();
    Notification::assertSentTo([$this->admin, $this->orderManager], DatabaseNotification::class, fn ($notification): bool => $notification->data['title'] === 'New order placed' && str_contains($notification->data['body'], $order->number));
    Notification::assertNotSentTo([$this->catalogManager, $this->customer], DatabaseNotification::class);
    Notification::assertCount(2);
});

it('notifies when a draft order is placed once', function () {
    Notification::fake();
    $order = Order::factory()->for($this->customer, 'customer')->create(['placed_at' => null]);
    Notification::assertNothingSent();
    $order->update(['placed_at' => now(), 'status' => 'confirmed']);
    $order->update(['shipping_total' => 100]);
    Notification::assertCount(2);
    Notification::assertSentTo($this->admin, DatabaseNotification::class, fn ($notification): bool => $notification->data['title'] === 'New order placed');
});

it('records actual status changes and ignores unchanged or unrelated updates', function () {
    $order = Order::factory()->for($this->customer, 'customer')->createQuietly();
    Notification::fake();
    $order->update(['status' => 'confirmed']);
    Notification::assertSentTo($this->admin, DatabaseNotification::class, fn ($notification): bool => str_contains($notification->data['body'], 'Pending Payment → Confirmed'));
    $order->update(['status' => 'confirmed', 'grand_total' => 1000]);
    $order->delete();
    $order->restore();
    Notification::assertCount(2);
});

it('excludes archived notification recipients', function () {
    $this->catalogManager->delete();
    Notification::fake();
    Product::factory()->create();
    Notification::assertNotSentTo($this->catalogManager, DatabaseNotification::class);
    Notification::assertCount(1);
});

it('stores notifications in the format read by the enabled panel bell', function () {
    config(['queue.default' => 'sync']);
    Product::factory()->create();
    $stored = $this->admin->notifications()->firstOrFail();
    expect($stored->data['format'])->toBe('filament')->and($stored->data['title'])->toBe('New product added')->and($stored->read_at)->toBeNull()
        ->and(Filament::getPanel('admin')->hasDatabaseNotifications())->toBeTrue();
    $stored->markAsRead();
    expect($this->admin->unreadNotifications()->count())->toBe(0);
});

it('does not queue notifications for rolled back changes', function () {
    config(['queue.default' => 'database']);
    $count = DB::table('jobs')->count();
    DB::beginTransaction();
    Product::factory()->create();
    DB::rollBack();
    expect(DB::table('jobs')->count())->toBe($count);
});

it('preserves separate status snapshots when an order changes repeatedly', function () {
    $order = Order::factory()->for($this->customer, 'customer')->createQuietly();
    Bus::fake();
    DB::transaction(function () use ($order): void {
        $order->update(['status' => 'confirmed']);
        $order->update(['status' => 'processing']);
    });
    Bus::assertDispatched(SendQueuedNotifications::class, fn ($job): bool => str_contains($job->notification->data['body'], 'Pending Payment → Confirmed'));
    Bus::assertDispatched(SendQueuedNotifications::class, fn ($job): bool => str_contains($job->notification->data['body'], 'Confirmed → Processing'));
});

it('queues only after commit and delivers a saved notification through the worker', function () {
    config(['queue.default' => 'database']);
    DB::beginTransaction();
    Product::factory()->create();
    expect(DB::table('jobs')->count())->toBe(0);
    DB::commit();
    expect(DB::table('jobs')->count())->toBe(2);
    $this->artisan('queue:work', ['--once' => true, '--tries' => 1])->assertSuccessful();
    expect(DB::table('notifications')->count())->toBe(1)
        ->and(DB::table('failed_jobs')->count())->toBe(0);
});

it('does not notify for catalog or user seed data', function () {
    Notification::fake();
    $this->seed(DatabaseSeeder::class);
    Notification::assertNothingSent();
});
