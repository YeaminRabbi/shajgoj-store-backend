<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage orders');
    }

    public function view(User $user, Model $record): bool
    {
        return $user->can('manage orders');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Model $record): bool
    {
        return $user->can('manage orders');
    }

    public function delete(User $user, Model $record): bool
    {
        return $user->can('manage orders');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('manage orders');
    }

    public function restore(User $user, Model $record): bool
    {
        return $user->can('manage orders');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('manage orders');
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
