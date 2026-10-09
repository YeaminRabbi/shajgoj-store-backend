<?php

namespace App\Services;

use App\Exceptions\DeletionBlockedException;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

class DeletionDependencies
{
    public function assertCanDelete(Model $record): void
    {
        $reason = $this->reason($record);
        if ($reason !== null) {
            throw new DeletionBlockedException($reason);
        }
    }

    public function reason(Model $record): ?string
    {
        $connection = $record->getConnection();
        $schema = $connection->getSchemaBuilder();
        $dependencies = [];
        foreach ($schema->getTables() as $table) {
            $name = $table['name'];
            if ($record instanceof Product && ! $record->isForceDeleting() && $name === 'product_variants') {
                continue;
            }
            $columns = [];
            foreach ($schema->getForeignKeys($name) as $foreignKey) {
                if ($foreignKey['foreign_table'] === $record->getTable() && $foreignKey['foreign_columns'] === [$record->getKeyName()]) {
                    $columns = array_merge($columns, $foreignKey['columns']);
                }
            }
            if ($columns === []) {
                continue;
            }
            $count = $connection->table($name)->where(function (Builder $query) use ($columns, $record): void {
                foreach ($columns as $column) {
                    $query->orWhere($column, $record->getKey());
                }
            })->count();
            if ($count > 0) {
                $label = $name === 'categories' ? 'child category' : Str::singular(str_replace('_', ' ', $name));
                $dependencies[] = $count.' linked '.Str::plural($label, $count);
            }
        }
        if ($dependencies === []) {
            return null;
        }
        $label = Str::lower(Str::headline(class_basename($record)));

        return 'Cannot delete this '.$label.' because it has '.implode(' and ', $dependencies).'. Remove or reassign the linked records first.';
    }
}
