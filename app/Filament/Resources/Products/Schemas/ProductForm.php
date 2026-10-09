<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Forms\Components\CatalogImageUpload;
use App\Models\ProductVariant;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    Section::make('Product details')
                        ->description('Name, identifiers, and descriptions customers will see.')
                        ->icon(Heroicon::OutlinedShoppingBag)
                        ->columns(['default' => 1, 'md' => 2])
                        ->schema([
                            TextInput::make('name')->label('Product name')->required()->maxLength(190),
                            TextInput::make('name_bn')->label('Bangla name')->maxLength(190),
                            TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                            TextInput::make('sku')->label('SKU')->required()->maxLength(255)->unique(ignoreRecord: true),
                            Textarea::make('description')->rows(4)->columnSpanFull(),
                            Textarea::make('description_bn')->label('Bangla description')->rows(4)->columnSpanFull(),
                        ]),
                    Section::make('Pricing')
                        ->description('Set the selling price and any quantity discounts.')
                        ->icon(Heroicon::OutlinedBanknotes)
                        ->columns(['default' => 1, 'md' => 3])
                        ->schema([
                            TextInput::make('price')->label('Selling price')->numeric()->minValue(0)->prefix('৳')->required(),
                            TextInput::make('compare_price')->label('Compare-at price')->numeric()->minValue(0)->prefix('৳'),
                            TextInput::make('tax_rate_override')->label('Tax rate override')->numeric()->suffix('%')->minValue(0)->maxValue(100),
                            Repeater::make('tier_prices')->label('Quantity discounts')->defaultItems(0)
                                ->schema([
                                    TextInput::make('min')->label('Minimum quantity')->integer()->minValue(1)->required(),
                                    TextInput::make('price')->label('Unit price')->numeric()->minValue(0)->prefix('৳')->required(),
                                ])
                                ->columns(['default' => 1, 'sm' => 2])
                                ->addActionLabel('Add quantity discount')
                                ->columnSpanFull(),
                        ]),
                    Section::make('Product images')
                        ->description('Choose a main image and arrange the gallery in display order.')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->schema([
                            CatalogImageUpload::make('primary_image')->label('Main image')->directory('products'),
                            CatalogImageUpload::make('gallery')->label('Image gallery')->multiple()->reorderable()->directory('products'),
                        ]),
                    Section::make('Variants & inventory')
                        ->description('Manage each product option, its SKU, price, and available stock.')
                        ->icon(Heroicon::OutlinedCube)
                        ->schema([
                            Repeater::make('variants')->hiddenLabel()->relationship()
                                ->schema([
                                    TextInput::make('name')->label('Variant name')->required()->maxLength(255),
                                    TextInput::make('sku')->label('SKU')->required()->maxLength(255)->distinct()->unique(table: ProductVariant::class, ignoreRecord: true),
                                    TextInput::make('price')->label('Variant price')->numeric()->minValue(0)->prefix('৳'),
                                    TextInput::make('stock')->label('Stock quantity')->integer()->required()->minValue(fn (?ProductVariant $record): int => $record?->reserved_stock ?? 0)->default(0),
                                    Toggle::make('is_active')->label('Active')->default(true),
                                ])
                                ->columns(['default' => 1, 'md' => 2])
                                ->addActionLabel('Add variant')
                                ->columnSpanFull(),
                        ]),
                    Section::make('Additional attributes')
                        ->description('Optional specifications such as material, color, or size.')
                        ->icon(Heroicon::OutlinedListBullet)
                        ->collapsible()
                        ->schema([
                            KeyValue::make('attributes')->hiddenLabel()->keyLabel('Attribute')->valueLabel('Value')->columnSpanFull(),
                        ]),
                ])->columnSpan(['default' => 1, 'lg' => 2]),
                Group::make([
                    Section::make('Organization')
                        ->icon(Heroicon::OutlinedSquares2x2)
                        ->schema([
                            Select::make('category_id')->relationship('category', 'name')->searchable()->preload()->required(),
                            Select::make('brand_id')->relationship('brand', 'name')->searchable()->preload(),
                            Select::make('type')->label('Product type')->options(['stock' => 'Local stock', 'sourcing' => 'Platform sourcing'])->required()->default('stock'),
                        ]),
                    Section::make('Publishing')
                        ->description('Control visibility and featured placement.')
                        ->icon(Heroicon::OutlinedEye)
                        ->schema([
                            Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'suspended' => 'Suspended'])->required()->default('draft'),
                            Toggle::make('featured')->label('Featured product'),
                        ]),
                    Section::make('Shipping & ordering')
                        ->icon(Heroicon::OutlinedTruck)
                        ->schema([
                            TextInput::make('weight_kg')->label('Shipping weight')->numeric()->minValue(0)->default(0.5)->suffix('kg')->required(),
                            TextInput::make('moq')->label('Minimum order quantity')->integer()->minValue(1)->default(1)->required(),
                        ]),
                ])->columnSpan(['default' => 1, 'lg' => 1]),
            ]);
    }
}
