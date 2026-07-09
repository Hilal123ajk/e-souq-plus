<?php

declare(strict_types=1);

return [
    'standard_delivery_fee' => 25,

    'admin_otp_valid_days' => 7,
    'admin_otp_expiry_minutes' => 15,

    'seo' => [
        'site_name' => 'E-Souq Plus',
        'default_description' => 'Shop carpets, artificial jewelry, stones & beads, perfumes and more at E-Souq Plus. Cash on delivery across the UAE.',
        'default_image' => '/banners/carpets.jpg',
        'locale' => 'en_AE',
        'twitter_handle' => null,
    ],

    /*
    | Static homepage hero carousel — not driven by admin categories.
    | Images live in public/banners/
    */
    'hero_banners' => [
        [
            'title' => 'Carpets',
            'subtitle' => 'Handwoven and modern carpets to elevate every room in your home.',
            'slug' => 'carpets',
            'image' => 'banners/carpets.jpg',
            'badge' => 'Carpets',
            'cta' => 'Shop Carpets',
        ],
        [
            'title' => 'Artificial Jewelry',
            'subtitle' => 'Elegant artificial jewelry for everyday wear and special occasions.',
            'slug' => 'artificial-jewelry',
            'image' => 'banners/artificial-jewelry.jpg',
            'badge' => 'Jewelry',
            'cta' => 'Shop Jewelry',
        ],
        [
            'title' => 'Stone and Beads',
            'subtitle' => 'Natural stones, beads, and rings crafted for timeless style.',
            'slug' => 'stones-and-beads',
            'image' => 'banners/stone-&-bead.jpg',
            'badge' => 'Stones & Beads',
            'cta' => 'Shop Stones',
        ],
        [
            'title' => 'Perfumes',
            'subtitle' => 'Premium fragrances and perfumes for men and women.',
            'slug' => 'perfumes',
            'image' => 'banners/perfumes.jpg',
            'badge' => 'Perfumes',
            'cta' => 'Shop Perfumes',
        ],
    ],

    'hero_side_promos' => [
        [
            'title' => 'Stone and Beads',
            'slug' => 'stones-and-beads',
            'image' => 'banners/stone-&-bead.jpg',
        ],
        [
            'title' => 'Perfumes',
            'slug' => 'perfumes',
            'image' => 'banners/perfumes.jpg',
        ],
    ],
];
