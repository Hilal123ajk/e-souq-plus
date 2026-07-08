<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\StoreCatalogService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly StoreCatalogService $catalog,
    ) {}

    public function index(): View
    {
        $categories = $this->catalog->getCategoriesForStore();

        return view('home', [
            'featuredProducts' => $this->catalog->getShuffledProductsPaginated(8),
            'newArrivals' => $this->catalog->getNewArrivals(5),
            'heroBanners' => $this->catalog->getHeroSlidesFromCategories(4),
            'homeCategories' => $categories,
            'sidePromoCategories' => $this->catalog->getSidePromoCategories(4, 2),
        ]);
    }

    public function categories(): View
    {
        return view('categories.index');
    }
}
