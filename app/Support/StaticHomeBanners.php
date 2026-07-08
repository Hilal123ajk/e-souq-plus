<?php

declare(strict_types=1);

namespace App\Support;

final class StaticHomeBanners
{
    /**
     * @return list<array{title: string, subtitle: string, slug: string, image: string, link: string, accent: string, badge: string, cta: string}>
     */
    public static function slides(): array
    {
        $accents = [
            'from-violet-600/90 to-indigo-900/70',
            'from-rose-700/80 to-purple-900/50',
            'from-amber-700/80 to-orange-900/50',
            'from-slate-900/85 to-slate-900/40',
        ];

        return collect(config('esouq.hero_banners', []))
            ->values()
            ->map(function (array $banner, int $index) use ($accents): array {
                return [
                    'title' => $banner['title'],
                    'subtitle' => $banner['subtitle'],
                    'slug' => $banner['slug'],
                    'image' => asset($banner['image']),
                    'link' => route('store.categories.show', $banner['slug']),
                    'accent' => $accents[$index % count($accents)],
                    'badge' => $banner['badge'] ?? $banner['title'],
                    'cta' => $banner['cta'] ?? 'Shop Now',
                ];
            })
            ->all();
    }

    /**
     * @return list<array{name: string, slug: string, image: string, url: string}>
     */
    public static function sidePromos(): array
    {
        return collect(config('esouq.hero_side_promos', []))
            ->map(fn (array $promo): array => [
                'name' => $promo['title'],
                'slug' => $promo['slug'],
                'image' => asset($promo['image']),
                'url' => route('store.categories.show', $promo['slug']),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{name: string, slug: string, image: string}>
     */
    public static function categoryTiles(): array
    {
        return collect(config('esouq.hero_banners', []))
            ->map(fn (array $banner): array => [
                'name' => $banner['title'],
                'slug' => $banner['slug'],
                'image' => asset($banner['image']),
            ])
            ->values()
            ->all();
    }
}
