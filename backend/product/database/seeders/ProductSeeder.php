<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        Product::query()->delete();
        Review::query()->delete();

        // Sample products with correct image paths
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with A17 Pro chip and titanium design',
                'price' => 999.99,
                'image' => 'images/products/iphone15.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'AI-powered smartphone with amazing camera',
                'price' => 899.99,
                'image' => 'images/products/galaxy-s24.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MacBook Pro 16"',
                'description' => 'Professional laptop with M3 Max chip',
                'price' => 2499.99,
                'image' => 'images/products/macbook-pro.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'description' => 'Noise cancelling wireless headphones',
                'price' => 349.99,
                'image' => 'images/products/sony-headphones.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'iPad Air',
                'description' => 'Powerful tablet with M1 chip',
                'price' => 599.99,
                'image' => 'images/products/ipad-air.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dell XPS 13',
                'description' => 'Ultra-thin laptop with InfinityEdge display',
                'price' => 1199.99,
                'image' => 'images/products/dell-xps.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Apple Watch Series 9',
                'description' => 'Smartwatch with health monitoring features',
                'price' => 399.99,
                'image' => 'images/products/apple-watch.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Bose QuietComfort',
                'description' => 'Premium noise cancelling earbuds',
                'price' => 279.99,
                'image' => 'images/products/bose-earbuds.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Google Pixel 8 Pro',
                'description' => 'Android phone with best-in-class camera',
                'price' => 799.99,
                'image' => 'images/products/pixel-8.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'PlayStation 5',
                'description' => 'Next-gen gaming console',
                'price' => 499.99,
                'image' => 'images/products/ps5.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Xbox Series X',
                'description' => 'Powerful gaming console with 4K gaming',
                'price' => 499.99,
                'image' => 'images/products/xbox.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Nintendo Switch OLED',
                'description' => 'Hybrid gaming console',
                'price' => 349.99,
                'image' => 'images/products/switch.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Samsung 4K TV',
                'description' => '55-inch 4K Smart TV with HDR',
                'price' => 699.99,
                'image' => 'images/products/samsung-tv.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'LG OLED TV',
                'description' => '65-inch OLED TV with perfect blacks',
                'price' => 1799.99,
                'image' => 'images/products/lg-tv.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Dyson V15 Vacuum',
                'description' => 'Cordless vacuum with laser detection',
                'price' => 749.99,
                'image' => 'images/products/dyson.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Insert products
        foreach ($products as $product) {
            Product::create($product);
        }

        // Add reviews for some products
        $reviewData = [
            ['reviewer_name' => 'John Doe', 'rating' => 5, 'comment' => 'Excellent product!'],
            ['reviewer_name' => 'Jane Smith', 'rating' => 4, 'comment' => 'Very good, but a bit expensive'],
            ['reviewer_name' => 'Mike Johnson', 'rating' => 5, 'comment' => 'Best purchase ever!'],
            ['reviewer_name' => 'Sarah Williams', 'rating' => 3, 'comment' => 'Good but could be better'],
            ['reviewer_name' => 'David Brown', 'rating' => 4, 'comment' => 'Satisfied with my purchase'],
            ['reviewer_name' => 'Emily Davis', 'rating' => 5, 'comment' => 'Absolutely love it!'],
            ['reviewer_name' => 'Chris Wilson', 'rating' => 2, 'comment' => 'Had some issues'],
            ['reviewer_name' => 'Lisa Taylor', 'rating' => 4, 'comment' => 'Great value for money'],
            ['reviewer_name' => 'Kevin Moore', 'rating' => 5, 'comment' => 'Exceeded my expectations'],
            ['reviewer_name' => 'Amy Anderson', 'rating' => 3, 'comment' => 'Average product'],
        ];

        // Add reviews to random products
        $products = Product::all();
        
        foreach ($products as $product) {
            // Add 2-4 reviews per product
            $reviewCount = rand(2, 4);
            
            for ($i = 0; $i < $reviewCount; $i++) {
                $randomReview = $reviewData[array_rand($reviewData)];
                
                Review::create([
                    'product_id' => $product->id,
                    'reviewer_name' => $randomReview['reviewer_name'],
                    'rating' => $randomReview['rating'],
                    'comment' => $randomReview['comment'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $this->command->info('✅ 15 products with reviews seeded successfully!');
    }
}