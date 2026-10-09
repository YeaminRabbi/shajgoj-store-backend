<?php

namespace App\Filament\Actions;

use App\Exceptions\DeletionBlockedException;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Throwable;

class DependencyAwareDeleteBulkAction extends DeleteBulkAction
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->authorizeIndividualRecords();
        $this->using(function (Collection|LazyCollection $records): void {
            $warnings = [];
            foreach ($records as $record) {
                try {
                    if (! $record->delete()) {
                        $this->reportBulkProcessingFailure();
                    }
                } catch (DeletionBlockedException $exception) {
                    $this->reportBulkProcessingFailure('dependencies', $exception->getMessage());
                    $warnings[] = $exception->getMessage();
                } catch (Throwable $exception) {
                    $this->reportBulkProcessingFailure();
                    report($exception);
                }
            }
            if ($warnings !== []) {
                Notification::make()->title('Deletion blocked')->body(implode(' ', array_unique($warnings)))->warning()->send();
            }
        });
    }
}
