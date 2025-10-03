<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        
        $products = [
            // Electronics
            [
                'name' => 'iPhone 15 Pro',
                'slug' => 'iphone-15-pro',
                'description' => 'The latest iPhone with advanced camera system and A17 Pro chip',
                'short_description' => 'Latest iPhone with advanced features',
                'price' => 999.00,
                'sale_price' => 899.00,
                'sku' => 'IPH15PRO-001',
                'stock_quantity' => 50,
                'category_id' => $categories->where('slug', 'electronics')->first()->id,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'slug' => 'samsung-galaxy-s24',
                'description' => 'Premium Android smartphone with exceptional camera quality',
                'short_description' => 'Premium Android smartphone',
                'price' => 799.00,
                'sku' => 'SGS24-001',
                'stock_quantity' => 30,
                'category_id' => $categories->where('slug', 'electronics')->first()->id,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'MacBook Air M3',
                'slug' => 'macbook-air-m3',
                'description' => 'Ultra-thin laptop with M3 chip for exceptional performance',
                'short_description' => 'Ultra-thin laptop with M3 chip',
                'price' => 1299.00,
                'sku' => 'MBA-M3-001',
                'stock_quantity' => 25,
                'category_id' => $categories->where('slug', 'electronics')->first()->id,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            
            // Clothing
            [
                'name' => 'Classic Cotton T-Shirt',
                'slug' => 'classic-cotton-t-shirt',
                'description' => 'Comfortable cotton t-shirt perfect for everyday wear',
                'short_description' => 'Comfortable cotton t-shirt',
                'price' => 29.99,
                'sku' => 'CCT-001',
                'stock_quantity' => 100,
                'category_id' => $categories->where('slug', 'clothing')->first()->id,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Denim Jeans',
                'slug' => 'denim-jeans',
                'description' => 'Classic blue denim jeans with perfect fit',
                'short_description' => 'Classic blue denim jeans',
                'price' => 79.99,
                'sku' => 'DJ-001',
                'stock_quantity' => 75,
                'category_id' => $categories->where('slug', 'clothing')->first()->id,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
            
            // Home & Garden
            [
                'name' => 'Smart Home Speaker',
                'slug' => 'smart-home-speaker',
                'description' => 'Voice-controlled smart speaker with excellent sound quality',
                'short_description' => 'Voice-controlled smart speaker',
                'price' => 149.99,
                'sku' => 'SHS-001',
                'stock_quantity' => 40,
                'category_id' => $categories->where('slug', 'home-garden')->first()->id,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Garden Tool Set',
                'slug' => 'garden-tool-set',
                'description' => 'Complete set of professional garden tools',
                'short_description' => 'Complete garden tool set',
                'price' => 89.99,
                'sku' => 'GTS-001',
                'stock_quantity' => 60,
                'category_id' => $categories->where('slug', 'home-garden')->first()->id,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 7,
            ],
            
            // Sports & Outdoors
            [
                'name' => 'Running Shoes',
                'slug' => 'running-shoes',
                'description' => 'Comfortable running shoes with advanced cushioning',
                'short_description' => 'Comfortable running shoes',
                'price' => 129.99,
                'sku' => 'RS-001',
                'stock_quantity' => 80,
                'category_id' => $categories->where('slug', 'sports-outdoors')->first()->id,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Yoga Mat',
                'slug' => 'yoga-mat',
                'description' => 'Premium yoga mat with excellent grip and cushioning',
                'short_description' => 'Premium yoga mat',
                'price' => 49.99,
                'sku' => 'YM-001',
                'stock_quantity' => 90,
                'category_id' => $categories->where('slug', 'sports-outdoors')->first()->id,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 9,
            ],
            
            // Books
            [
                'name' => 'Programming Fundamentals',
                'slug' => 'programming-fundamentals',
                'description' => 'Comprehensive guide to programming concepts and practices',
                'short_description' => 'Comprehensive programming guide',
                'price' => 39.99,
                'sku' => 'PF-001',
                'stock_quantity' => 120,
                'category_id' => $categories->where('slug', 'books')->first()->id,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 10,
            ],
            
            // Health & Beauty
            [
                'name' => 'Skincare Set',
                'slug' => 'skincare-set',
                'description' => 'Complete skincare routine with natural ingredients',
                'short_description' => 'Complete skincare routine',
                'price' => 69.99,
                'sku' => 'SS-001',
                'stock_quantity' => 70,
                'category_id' => $categories->where('slug', 'health-beauty')->first()->id,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 11,
            ],
            
            // Toys & Games
            [
                'name' => 'Educational Building Blocks',
                'slug' => 'educational-building-blocks',
                'description' => 'Colorful building blocks for creative play and learning',
                'short_description' => 'Colorful building blocks',
                'price' => 34.99,
                'sku' => 'EBB-001',
                'stock_quantity' => 150,
                'category_id' => $categories->where('slug', 'toys-games')->first()->id,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 12,
            ],
            
            // Automotive
            [
                'name' => 'Car Phone Mount',
                'slug' => 'car-phone-mount',
                'description' => 'Secure phone mount for car dashboard',
                'short_description' => 'Secure car phone mount',
                'price' => 24.99,
                'sku' => 'CPM-001',
                'stock_quantity' => 200,
                'category_id' => $categories->where('slug', 'automotive')->first()->id,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 13,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
