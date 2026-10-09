<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Actions\DependencyAwareDeleteBulkAction as DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('primary_image')->disk('public')->circular(),
                TextColumn::make('name')->searchable()->sortable()->limit(40),
                TextColumn::make('category.name')->sortable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('price')->money('BDT')->sortable(),
                TextColumn::make('status')->badge(),
                IconColumn::make('featured')->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'suspended' => 'Suspended']),
                SelectFilter::make('type')->options(['stock' => 'Stock', 'sourcing' => 'Sourcing']),
            ])
            ->recordActions([
                RestoreAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
