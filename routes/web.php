<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Store\CategoryController as StoreCategoryController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\OrderController as StoreOrderController;
use App\Http\Controllers\Store\ProductController as StoreProductController;
use App\Http\Controllers\Store\SitemapController;
use App\Http\Controllers\Store\StripeCheckoutController;
use Illuminate\Support\Facades\Route;

Route::name('store.')->group(function () {
    Route::get('/robots.txt', function () {
        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /checkout',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    })->name('robots');

    Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/categories/all', [StoreProductController::class, 'index'])->name('products.index');
    Route::get('/products/{slug}', [StoreProductController::class, 'show'])->name('products.show');

    Route::get('/categories', [HomeController::class, 'categories'])->name('categories.index');
    Route::get('/categories/{parentSlug}/{slug}', [StoreCategoryController::class, 'showSubcategory'])->name('categories.sub.show');
    Route::get('/categories/{slug}', [StoreCategoryController::class, 'show'])->name('categories.show');

    Route::get('/checkout', fn () => view('checkout'))->name('checkout');
    Route::post('/orders', [StoreOrderController::class, 'store'])
        ->middleware('throttle:store-orders')
        ->name('orders.store');
    Route::post('/checkout/stripe', [StripeCheckoutController::class, 'store'])
        ->middleware('throttle:store-orders')
        ->name('checkout.stripe.store');
    Route::get('/checkout/stripe/success', [StripeCheckoutController::class, 'success'])
        ->name('checkout.stripe.success');
    Route::get('/checkout/stripe/cancel/{order}', [StripeCheckoutController::class, 'cancel'])
        ->name('checkout.stripe.cancel');
    Route::post('/stripe/webhook', [StripeCheckoutController::class, 'webhook'])
        ->name('stripe.webhook');
    Route::redirect('/cart', '/checkout')->name('cart');

    Route::get('/about-us', fn () => view('pages.about'))->name('pages.about');
    Route::get('/contact-us', fn () => view('pages.contact'))->name('pages.contact');
    Route::get('/faqs', function () {
        return view('pages.faqs', [
            'faqs' => [
                [
                    'question' => 'Are the products genuine?',
                    'answer' => 'Yes — every product on E-Souq Plus is sourced from trusted suppliers. We do not sell copies or low-grade imitations. Contact us before or after your order if you have any authenticity concerns.',
                ],
                [
                    'question' => 'Do you offer Cash on Delivery (COD)?',
                    'answer' => 'Yes, we offer Cash on Delivery across major cities in the UAE including Dubai, Abu Dhabi, Sharjah, Ajman, and Ras Al Khaimah. Pay the courier when your package arrives — no advance payment needed.',
                ],
                [
                    'question' => 'How long does delivery take?',
                    'answer' => 'Orders are processed within 1–2 business days. Delivery to major cities takes 2–4 business days. Other areas may take 4–7 business days. You will receive updates via phone or WhatsApp once dispatched.',
                ],
                [
                    'question' => 'What if I receive a damaged or wrong item?',
                    'answer' => 'Contact us on WhatsApp or phone immediately. We will arrange a replacement or return at no extra cost. Please share photos of the item and packaging for faster resolution.',
                ],
                [
                    'question' => 'Can I order without creating an account?',
                    'answer' => 'Yes. E-Souq Plus does not require an account. Browse products, add to cart, and checkout with your name, phone, and address. We use your phone number to confirm and track orders.',
                ],
                [
                    'question' => 'What categories do you sell?',
                    'answer' => 'We currently offer mobile accessories, furniture, home décor, electronics, kitchen items, and fashion. We are continuously adding new categories to become your one-stop online marketplace.',
                ],
            ],
        ]);
    })->name('pages.faqs');
    Route::get('/returns-and-exchange', fn () => view('pages.returns'))->name('pages.returns');
    Route::get('/shipping-policy', fn () => view('pages.shipping'))->name('pages.shipping');
    Route::get('/delivery-process', fn () => view('pages.shipping'))->name('pages.delivery');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:admin-login')
            ->name('login.submit');
        Route::get('/login/verify', [AuthController::class, 'showVerifyOtp'])->name('login.verify');
        Route::post('/login/verify', [AuthController::class, 'verifyOtp'])
            ->middleware('throttle:admin-otp')
            ->name('login.verify.submit');
        Route::post('/login/verify/resend', [AuthController::class, 'resendOtp'])
            ->middleware('throttle:admin-otp-resend')
            ->name('login.verify.resend');
    });

    Route::middleware('admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');

        Route::get('/products', [ProductController::class, 'index'])->name('products');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/{productId}/restore', [ProductController::class, 'restore'])->name('products.restore');
        Route::delete('/products/{productId}/force', [ProductController::class, 'forceDestroy'])->name('products.force-destroy');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/categories/{categoryId}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
        Route::delete('/categories/{categoryId}/force', [CategoryController::class, 'forceDestroy'])->name('categories.force-destroy');

        Route::get('/sub-categories', [SubCategoryController::class, 'index'])->name('subcategories');
        Route::post('/sub-categories', [SubCategoryController::class, 'store'])->name('subcategories.store');
        Route::put('/sub-categories/{category}', [SubCategoryController::class, 'update'])->name('subcategories.update');
        Route::delete('/sub-categories/{category}', [SubCategoryController::class, 'destroy'])->name('subcategories.destroy');
        Route::post('/sub-categories/{categoryId}/restore', [SubCategoryController::class, 'restore'])->name('subcategories.restore');
        Route::delete('/sub-categories/{categoryId}/force', [SubCategoryController::class, 'forceDestroy'])->name('subcategories.force-destroy');

        Route::get('/brands', [BrandController::class, 'index'])->name('brands');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders');
        Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('/customers', fn () => view('admin.customers.index'))->name('customers');
    });
});
