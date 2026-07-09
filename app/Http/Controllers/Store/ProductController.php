<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Seo;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.index', [
            'seo' => [
                'title' => 'All Products',
                'description' => 'Browse all products at E-Souq Plus. Carpets, jewelry, perfumes, home décor and more with cash on delivery.',
                'url' => route('store.products.index'),
                'type' => 'website',
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with('images')
            ->first();

        $seo = null;
        $structuredData = null;

        if ($product !== null) {
            $seo = Seo::forProduct($product);
            $structuredData = Seo::productStructuredData($product, $seo);
        }

        return view('products.show', compact('slug', 'product', 'seo', 'structuredData'));
    }
}
