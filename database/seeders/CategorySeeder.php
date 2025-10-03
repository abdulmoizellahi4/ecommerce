<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Latest electronic devices and gadgets',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Fashion and apparel for all ages',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'description' => 'Home improvement and garden supplies',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Sports & Outdoors',
                'slug' => 'sports-outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Books',
                'slug' => 'books',
                'description' => 'Books, magazines, and educational materials',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Health & Beauty',
                'slug' => 'health-beauty',
                'description' => 'Health products and beauty supplies',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Toys & Games',
                'slug' => 'toys-games',
                'description' => 'Toys, games, and entertainment',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'description' => 'Car parts and automotive accessories',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
