<?php

namespace App\Models\Concerns;

use App\Services\DeletionDependencies;
use Illuminate\Database\Eloquent\Model;

trait ChecksDeletionDependencies
{
    public static function bootChecksDeletionDependencies(): void
    {
        static::deleting(function (Model $record): void {
            app(DeletionDependencies::class)->assertCanDelete($record);
        });
    }
}
