<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CatalogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage catalog');
    }

    public function view(User $user, Model $record): bool
    {
        return $user->can('manage catalog');
    }

    public function create(User $user): bool
    {
        return $user->can('manage catalog');
    }

    public function update(User $user, Model $record): bool
    {
        return $user->can('manage catalog');
    }

    public function delete(User $user, Model $record): bool
    {
        return $user->can('manage catalog');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('manage catalog');
    }

    public function restore(User $user, Model $record): bool
    {
        return $user->can('manage catalog');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('manage catalog');
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
