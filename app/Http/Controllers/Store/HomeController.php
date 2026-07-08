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
        ]);
    }

    public function categories(): View
    {
        return view('categories.index');
    }
}
