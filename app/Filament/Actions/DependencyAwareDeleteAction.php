<?php

namespace App\Filament\Actions;

use App\Exceptions\DeletionBlockedException;
use App\Services\DeletionDependencies;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class DependencyAwareDeleteAction extends DeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mountUsing(function (Model $record): void {
            $reason = app(DeletionDependencies::class)->reason($record);

            if ($reason !== null) {
                Notification::make()->title('Deletion blocked')->body($reason)->warning()->send();
                $this->halt();
            }
        });

        $this->using(function (Model $record): ?bool {
            try {
                return $record->delete();
            } catch (DeletionBlockedException $exception) {
                Notification::make()->title('Deletion blocked')->body($exception->getMessage())->warning()->send();
                $this->halt();
            }
        });
    }
}
