<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\StoreCatalogService;
use App\Support\StaticHomeBanners;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly StoreCatalogService $catalog,
    ) {}

    public function index(): View
    {
        return view('home', [
            'featuredProducts' => $this->catalog->getShuffledProductsPaginated(8),
            'newArrivals' => $this->catalog->getNewArrivals(5),
            'heroBanners' => StaticHomeBanners::slides(),
            'sidePromoCategories' => StaticHomeBanners::sidePromos(),
            'staticCategoryTiles' => StaticHomeBanners::categoryTiles(),
            'seo' => [
                'title' => 'Online Marketplace in UAE',
                'description' => config('esouq.seo.default_description'),
                'url' => route('store.home'),
                'image' => \App\Support\Seo::defaultImage(),
                'type' => 'website',
            ],
        ]);
    }

    public function categories(): View
    {
        return view('categories.index', [
            'seo' => [
                'title' => 'Shop by Category',
                'description' => 'Browse carpets, artificial jewelry, perfumes, stones & beads and more at E-Souq Plus.',
                'url' => route('store.categories.index'),
                'type' => 'website',
            ],
        ]);
    }
}
