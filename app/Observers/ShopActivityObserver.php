<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ShopNotifications;

class ShopActivityObserver
{
    public function __construct(private ShopNotifications $notifications) {}

    public function created(User|Order|Product $record): void
    {
        if ($record instanceof User) {
            $this->notifications->userRegistered($record);
        } elseif ($record instanceof Product) {
            $this->notifications->productAdded($record);
        } elseif ($record->placed_at !== null) {
            $this->notifications->orderPlaced($record);
        }
    }

    public function updated(User|Order|Product $record): void
    {
        if (! $record instanceof Order) {
            return;
        }

        if ($record->wasChanged('placed_at') && $record->getRawOriginal('placed_at') === null && $record->placed_at !== null) {
            $this->notifications->orderPlaced($record);
        } elseif ($record->wasChanged('status')) {
            $this->notifications->orderStatusChanged($record, (string) $record->getRawOriginal('status'));
        }
    }
}
