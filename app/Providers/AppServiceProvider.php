<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Observers\ShopActivityObserver;
use App\Policies\CatalogPolicy;
use App\Policies\DeliveryZonePolicy;
use App\Policies\OrderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(ShopActivityObserver::class);
        Order::observe(ShopActivityObserver::class);
        Product::observe(ShopActivityObserver::class);

        Gate::policy(Product::class, CatalogPolicy::class);
        Gate::policy(Category::class, CatalogPolicy::class);
        Gate::policy(Brand::class, CatalogPolicy::class);
        Gate::policy(DeliveryZone::class, DeliveryZonePolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
    }
}
