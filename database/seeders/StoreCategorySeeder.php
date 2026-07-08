<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class StoreCategorySeeder extends Seeder
{
    /**
     * @var array<string, array{title: string, description: string, image: string}>
     */
    private const CATEGORIES = [
        'carpets' => [
            'title' => 'Carpets',
            'description' => 'Handwoven and modern carpets for every room.',
            'image' => 'images/categories/carpets.jpg',
        ],
        'artificial-jewelry' => [
            'title' => 'Artificial Jewelry',
            'description' => 'Elegant artificial jewelry for everyday and special occasions.',
            'image' => 'images/categories/artificial-jewelry.jpg',
        ],
        'perfumes' => [
            'title' => 'Perfumes',
            'description' => 'Fragrances and perfumes for men and women.',
            'image' => 'images/categories/perfumes.jpg',
        ],
        'traditional-things' => [
            'title' => 'Traditional Things',
            'description' => 'Traditional crafts, décor and cultural items.',
            'image' => 'images/categories/traditional-things.jpg',
        ],
        'stones-and-rings' => [
            'title' => 'Stones and Rings',
            'description' => 'Stones, rings and fine jewelry pieces.',
            'image' => 'images/categories/stones-rings.jpg',
        ],
        'mobile-accessories' => [
            'title' => 'Mobile Accessories',
            'description' => 'Cases, chargers, screen protectors and audio gear.',
            'image' => 'images/banners/mobile-accessories-1.jpg',
        ],
    ];

    public function run(): void
    {
        $storageDir = storage_path('app/public/categories');

        if (! File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        foreach (self::CATEGORIES as $slug => $config) {
            $source = public_path($config['image']);
            $storedPath = '';

            if (File::exists($source)) {
                $filename = $slug.'.'.pathinfo($source, PATHINFO_EXTENSION);
                $destination = $storageDir.'/'.$filename;

                if (! File::exists($destination)) {
                    File::copy($source, $destination);
                }

                $storedPath = 'categories/'.$filename;
            }

            Category::query()->updateOrCreate(
                ['slug' => $slug, 'parent_id' => null],
                [
                    'title' => $config['title'],
                    'description' => $config['description'],
                    'image_url' => $storedPath,
                    'is_active' => true,
                ]
            );
        }
    }
}
