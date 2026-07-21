<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unitId = Unit::where('name', '個')->value('id');

        $productsByShopSlug = [
            'shop1' => [
                ['name' => 'りんご', 'image_path' => 'products/product-apple.jpg', 'price' => 300, 'stock' => 50],
                ['name' => 'トマト', 'image_path' => 'products/product-tomato.jpg', 'price' => 200, 'stock' => 80],
                ['name' => 'にんじん', 'image_path' => 'products/product-carrot.jpg', 'price' => 150, 'stock' => 100],
            ],
            'shop2' => [
                ['name' => 'バナナ', 'image_path' => 'products/product-banana.jpg', 'price' => 180, 'stock' => 60],
                ['name' => 'いちご', 'image_path' => 'products/product-strawberry.jpg', 'price' => 500, 'stock' => 40],
                ['name' => 'たまねぎ', 'image_path' => 'products/product-onion.jpg', 'price' => 120, 'stock' => 90],
            ],
        ];

        foreach ($productsByShopSlug as $slug => $products) {
            $shop = Shop::where('slug', $slug)->first();

            if (! $shop) {
                continue;
            }

            foreach ($products as $product) {
                Product::firstOrCreate(
                    ['shop_id' => $shop->id, 'name' => $product['name']],
                    [
                        'image_path' => $product['image_path'],
                        'price' => $product['price'],
                        'stock' => $product['stock'],
                        'is_active' => true,
                        'unit_id' => $unitId,
                        'unit_quantity' => 1,
                    ]
                );
            }
        }
    }
}
