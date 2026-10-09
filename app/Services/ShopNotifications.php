<?php

namespace App\Services;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ShopNotifications
{
    public function userRegistered(User $user): void
    {
        $this->send(
            User::role('admin')->whereKeyNot($user->getKey()),
            Notification::make()->title('New user registered')->icon('heroicon-o-user-plus')
                ->body(e($user->name).' ('.e($user->email).') has registered.'),
        );
    }

    public function productAdded(Product $product): void
    {
        $this->send(
            User::permission('manage catalog'),
            Notification::make()->title('New product added')->icon('heroicon-o-shopping-bag')
                ->body(e($product->name).' has been added to the catalog.')
                ->actions([Action::make('view')->label('View product')->button()->markAsRead()
                    ->url(ProductResource::getUrl('edit', ['record' => $product], panel: 'admin'))]),
        );
    }

    public function orderPlaced(Order $order): void
    {
        $this->send(User::permission('manage orders'), $this->orderNotification($order)
            ->title('New order placed')->body('Order '.e($order->number).' has been placed.'));
    }

    public function orderStatusChanged(Order $order, string $previousStatus): void
    {
        $this->send(User::permission('manage orders'), $this->orderNotification($order)
            ->title('Order status changed')->body('Order '.e($order->number).': '
                .e(Str::headline($previousStatus)).' → '.e(Str::headline($order->status)).'.'));
    }

    private function orderNotification(Order $order): Notification
    {
        return Notification::make()->icon('heroicon-o-clipboard-document-list')
            ->actions([Action::make('view')->label('View order')->button()->markAsRead()
                ->url(OrderResource::getUrl('index', ['tableSearch' => $order->number], panel: 'admin'))]);
    }

    /** @param Builder<User> $recipients */
    private function send(Builder $recipients, Notification $notification): void
    {
        $recipients->lazyById(100)->each(function (User $recipient) use ($notification): void {
            $recipient->notify($notification->toDatabase()->afterCommit());
        });
    }
}
