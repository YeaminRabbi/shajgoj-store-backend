<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Forms\Components\CatalogImageUpload;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use BackedEnum;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('name_bn')->label('Bangla name')->maxLength(255),
                TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                Select::make('parent_id')->relationship('parent', 'name')->searchable()->preload()
                    ->rules([fn (?Category $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                        $visited = [];
                        $parentId = $value;
                        while ($parentId !== null) {
                            if ((string) $parentId === (string) $record?->id || in_array((string) $parentId, $visited, true)) {
                                $fail('A category cannot be its own parent or belong to one of its descendants.');

                                return;
                            }
                            $visited[] = (string) $parentId;
                            $parentId = Category::find($parentId)?->parent_id;
                        }
                    }]),
                CatalogImageUpload::make('image')->directory('categories'),
                TextInput::make('sort_order')->integer()->minValue(0)->default(0),
                Toggle::make('is_active')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('parent.name')->placeholder('Root'),
                TextColumn::make('slug'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->authorizeIndividualRecords(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
