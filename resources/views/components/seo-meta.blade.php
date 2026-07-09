@php
    use App\Support\Seo;

    $seoTitle = Seo::title($metaTitle ?? null);
    $seoDescription = Seo::description($metaDescription ?? null);
    $seoUrl = $metaUrl ?? url()->current();
    $seoImage = $metaImage ?? Seo::defaultImage();
    $seoType = $metaType ?? 'website';
    $seoSiteName = Seo::siteName();
    $seoLocale = config('esouq.seo.locale', 'en_AE');
    $twitterHandle = config('esouq.seo.twitter_handle');
@endphp

<link rel="canonical" href="{{ $seoUrl }}">
<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="index, follow">
<meta property="og:locale" content="{{ str_replace('_', '-', $seoLocale) }}">
<meta property="og:site_name" content="{{ $seoSiteName }}">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:alt" content="{{ $metaImageAlt ?? $seoTitle }}">
@if ($metaType === 'product' && filled($productPrice ?? null))
<meta property="product:price:amount" content="{{ $productPrice }}">
<meta property="product:price:currency" content="{{ $productCurrency ?? 'AED' }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
@if ($twitterHandle)
<meta name="twitter:site" content="{{ $twitterHandle }}">
@endif

@if (! empty($structuredData))
<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
