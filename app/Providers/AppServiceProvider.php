<?php

namespace App\Providers;

use App\Services\StoreCatalogService;
use App\Support\DeliveryPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $this->configureRateLimiting();

        View::composer('layouts.app', function ($view): void {
            $catalog = app(StoreCatalogService::class);

            $view->with([
                'storeCatalogCategories' => $catalog->getCategoriesForStore(),
                'storeCatalogProducts' => $catalog->getProductsForStore(),
                'storeCatalogBrands' => $catalog->getBrandsForStore(),
                'storeDeliveryConfig' => DeliveryPolicy::frontendConfig(),
            ]);
        });

        View::composer('components.header', function ($view): void {
            if (! $view->offsetExists('storeCatalogCategories')) {
                $view->with(
                    'storeCatalogCategories',
                    app(StoreCatalogService::class)->getCategoriesForStore()
                );
            }
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('admin-otp', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('admin-otp-resend', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));

        RateLimiter::for('store-orders', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
