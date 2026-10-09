<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DeliveryZonePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage delivery zones');
    }

    public function view(User $user, Model $record): bool
    {
        return $user->can('manage delivery zones');
    }

    public function create(User $user): bool
    {
        return $user->can('manage delivery zones');
    }

    public function update(User $user, Model $record): bool
    {
        return $user->can('manage delivery zones');
    }

    public function delete(User $user, Model $record): bool
    {
        return $user->can('manage delivery zones');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('manage delivery zones');
    }
}
