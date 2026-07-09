<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

final class Seo
{
    public static function siteName(): string
    {
        return (string) config('esouq.seo.site_name', 'E-Souq Plus');
    }

    public static function defaultDescription(): string
    {
        return (string) config('esouq.seo.default_description');
    }

    public static function defaultImage(): string
    {
        return self::absoluteUrl((string) config('esouq.seo.default_image', '/banners/carpets.jpg'));
    }

    public static function title(?string $pageTitle = null): string
    {
        if ($pageTitle === null || $pageTitle === '') {
            return self::siteName().' — Online Marketplace in UAE';
        }

        if (str_contains($pageTitle, self::siteName())) {
            return $pageTitle;
        }

        return $pageTitle.' — '.self::siteName();
    }

    public static function description(?string $description = null): string
    {
        $description = trim(strip_tags($description ?? ''));

        if ($description === '') {
            return self::defaultDescription();
        }

        return Str::limit($description, 160, '…');
    }

    public static function absoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url(ltrim($path, '/'));
    }

    public static function imageUrl(?string $path): string
    {
        if ($path === null || $path === '') {
            return self::defaultImage();
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url('storage/'.ltrim($path, '/'));
    }

    /**
     * @return array<string, mixed>
     */
    public static function forProduct(Product $product): array
    {
        $images = array_values(array_unique(array_filter([
            $product->image_public_url,
            ...$product->images->map(fn ($image) => $image->image_public_url)->all(),
        ])));

        return [
            'title' => $product->name,
            'description' => self::description($product->description ?: "Buy {$product->name} at E-Souq Plus. Cash on delivery across the UAE."),
            'url' => route('store.products.show', $product->slug),
            'image' => $images[0] ?? self::defaultImage(),
            'images' => $images !== [] ? $images : [self::defaultImage()],
            'type' => 'product',
            'price' => (float) $product->price,
            'currency' => 'AED',
            'sku' => $product->sku,
            'in_stock' => $product->stock_quantity > 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forCategory(Category $category): array
    {
        return [
            'title' => $category->title,
            'description' => self::description($category->description ?: "Shop {$category->title} at E-Souq Plus. Quality products with cash on delivery in the UAE."),
            'url' => $category->storeUrl(),
            'image' => $category->image_public_url !== '' ? $category->image_public_url : self::defaultImage(),
            'images' => [$category->image_public_url !== '' ? $category->image_public_url : self::defaultImage()],
            'type' => 'website',
        ];
    }

    /**
     * @param  array<string, mixed>  $seo
     * @return array<string, mixed>
     */
    public static function productStructuredData(Product $product, array $seo): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $seo['description'],
            'image' => $seo['images'],
            'sku' => $product->sku,
            'offers' => [
                '@type' => 'Offer',
                'url' => $seo['url'],
                'priceCurrency' => $seo['currency'],
                'price' => number_format((float) $product->price, 2, '.', ''),
                'availability' => $product->stock_quantity > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ],
        ];
    }
}
