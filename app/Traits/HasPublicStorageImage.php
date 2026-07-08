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

        $storagePath = '/storage/'.ltrim($path, '/');

        if (app()->runningInConsole()) {
            return asset(ltrim($storagePath, '/'));
        }

        return rtrim(request()->getSchemeAndHttpHost().request()->getBaseUrl(), '/').$storagePath;
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
