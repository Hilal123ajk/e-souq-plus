<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasPublicStorageImage
{
    public function getImagePublicUrlAttribute(): string
    {
        $path = $this->attributes['image_url'] ?? '';

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relativePath = '/storage/'.ltrim($path, '/');

        if (app()->runningInConsole()) {
            return rtrim((string) config('app.url'), '/').$relativePath;
        }

        return rtrim(request()->getSchemeAndHttpHost().request()->getBaseUrl(), '/').$relativePath;
    }

    public function getStoredImagePath(): string
    {
        return $this->attributes['image_url'] ?? '';
    }

    protected static function deleteStoredImage(self $model): void
    {
        $path = $model->getStoredImagePath();

        if ($path !== '' && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
