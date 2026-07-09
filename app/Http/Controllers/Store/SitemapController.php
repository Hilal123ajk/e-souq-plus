<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\Seo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('store.sitemap', now()->addHours(6), fn (): string => $this->buildXml());

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function buildXml(): string
    {
        $urls = [];

        $urls[] = $this->urlEntry(Seo::absoluteUrl('/'), now(), 'daily', '1.0');

        $staticPages = [
            '/categories',
            '/categories/all',
            '/about-us',
            '/contact-us',
            '/faqs',
            '/shipping-policy',
            '/returns-and-exchange',
        ];

        foreach ($staticPages as $path) {
            $urls[] = $this->urlEntry(Seo::absoluteUrl($path), now()->subDay(), 'weekly', '0.7');
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($categories as $category) {
            $urls[] = $this->urlEntry(
                Seo::absoluteUrl(parse_url($category->storeUrl(), PHP_URL_PATH) ?: '/categories'),
                $category->updated_at,
                'weekly',
                '0.8',
                [[
                    'loc' => $category->getStoredImagePath() !== ''
                        ? Seo::imageUrl($category->getStoredImagePath())
                        : Seo::defaultImage(),
                    'title' => $category->title,
                ]],
            );
        }

        $products = Product::query()
            ->where('is_active', true)
            ->with('images')
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($products as $product) {
            $images = array_values(array_unique(array_filter([
                $product->getStoredImagePath() !== '' ? Seo::imageUrl($product->getStoredImagePath()) : null,
                ...$product->images->map(fn ($image) => $image->getStoredImagePath() !== ''
                    ? Seo::imageUrl($image->getStoredImagePath())
                    : null)->all(),
            ])));

            if ($images === []) {
                $images[] = Seo::defaultImage();
            }

            $imageEntries = array_map(fn (string $imageUrl): array => [
                'loc' => $imageUrl,
                'title' => $product->name,
            ], $images);

            $urls[] = $this->urlEntry(
                route('store.products.show', $product->slug),
                $product->updated_at,
                'weekly',
                '0.9',
                $imageEntries,
            );
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            .'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'
            .implode('', $urls)
            .'</urlset>';
    }

    /**
     * @param  list<array{loc: string, title: string}>  $images
     */
    private function urlEntry(string $loc, mixed $lastmod, string $changefreq, string $priority, array $images = []): string
    {
        $lastmodDate = $lastmod?->toAtomString() ?? now()->toAtomString();

        $imageXml = '';
        foreach ($images as $image) {
            $imageXml .= '<image:image>'
                .'<image:loc>'.e($image['loc']).'</image:loc>'
                .'<image:title>'.e($image['title']).'</image:title>'
                .'</image:image>';
        }

        return '<url>'
            .'<loc>'.e($loc).'</loc>'
            .'<lastmod>'.e($lastmodDate).'</lastmod>'
            .'<changefreq>'.e($changefreq).'</changefreq>'
            .'<priority>'.e($priority).'</priority>'
            .$imageXml
            .'</url>';
    }
}
