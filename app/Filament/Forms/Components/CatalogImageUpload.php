<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;

class CatalogImageUpload extends FileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->maxSize(5120)->disk('public')->visibility('public')
            ->fetchFileInformation(false)->preventFilePathTampering();

        $this->getUploadedFileUsing(static function (BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array {
            if (Str::isUrl($file, ['http', 'https'])) {
                return ['name' => basename(parse_url($file, PHP_URL_PATH) ?: 'image'), 'size' => 0, 'type' => null, 'url' => $file];
            }

            return $component->getUploadedFile($file, $storedFileNames);
        });
    }
}
