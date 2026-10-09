<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CosmeticsCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Skin Care', 'name_bn' => 'স্কিন কেয়ার', 'slug' => 'skin-care', 'image' => 'https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8?auto=format&fit=crop&w=800&q=80'],
            ['name' => 'Makeup', 'name_bn' => 'মেকআপ', 'slug' => 'makeup', 'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=800&q=80'],
            ['name' => 'Hair Care', 'name_bn' => 'হেয়ার কেয়ার', 'slug' => 'hair-care', 'image' => 'https://images.unsplash.com/photo-1527799820374-dcf8d9d4a388?auto=format&fit=crop&w=800&q=80'],
            ['name' => 'Fragrance', 'name_bn' => 'সুগন্ধি', 'slug' => 'fragrance', 'image' => 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=800&q=80'],
            ['name' => 'Bath & Body', 'name_bn' => 'বাথ ও বডি', 'slug' => 'bath-body', 'image' => 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=800&q=80'],
        ];

        $categoryModels = collect($categories)->mapWithKeys(function (array $category, int $index): array {
            $model = Category::updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'sort_order' => $index + 20, 'is_active' => true],
            );

            return [$category['slug'] => $model];
        });

        $brand = Brand::updateOrCreate(
            ['slug' => 'cosmetic-essentials'],
            ['name' => 'Cosmetic Essentials', 'name_bn' => 'কসমেটিক এসেনশিয়ালস', 'status' => 'approved'],
        );

        $products = [
            ['sku' => 'COS-0001', 'slug' => 'vitamin-c-brightening-serum', 'name' => 'Vitamin C Brightening Serum', 'category' => 'skin-care', 'price' => 850, 'compare_price' => 999, 'stock' => 60, 'image' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=900&q=80', 'badge' => 'BESTSELLER'],
            ['sku' => 'COS-0002', 'slug' => 'hyaluronic-acid-moisturizer', 'name' => 'Hyaluronic Acid Moisturizer', 'category' => 'skin-care', 'price' => 720, 'compare_price' => 850, 'stock' => 75, 'image' => 'https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8?auto=format&fit=crop&w=900&q=80', 'badge' => null],
            ['sku' => 'COS-0003', 'slug' => 'gentle-foaming-face-cleanser', 'name' => 'Gentle Foaming Face Cleanser', 'category' => 'skin-care', 'price' => 540, 'compare_price' => 650, 'stock' => 80, 'image' => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=900&q=80', 'badge' => null],
            ['sku' => 'COS-0004', 'slug' => 'spf-50-sunscreen-gel', 'name' => 'SPF 50 Sunscreen Gel', 'category' => 'skin-care', 'price' => 680, 'compare_price' => 800, 'stock' => 100, 'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=900&q=80', 'badge' => 'NEW'],
            ['sku' => 'COS-0005', 'slug' => 'matte-liquid-foundation', 'name' => 'Matte Liquid Foundation', 'category' => 'makeup', 'price' => 950, 'compare_price' => 1100, 'stock' => 50, 'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80', 'badge' => null],
            ['sku' => 'COS-0006', 'slug' => 'waterproof-lengthening-mascara', 'name' => 'Waterproof Lengthening Mascara', 'category' => 'makeup', 'price' => 520, 'compare_price' => 620, 'stock' => 70, 'image' => 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=900&q=80', 'badge' => null],
            ['sku' => 'COS-0007', 'slug' => 'velvet-matte-lipstick-set', 'name' => 'Velvet Matte Lipstick Set', 'category' => 'makeup', 'price' => 780, 'compare_price' => 900, 'stock' => 65, 'image' => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=900&q=80', 'badge' => 'HOT'],
            ['sku' => 'COS-0008', 'slug' => 'professional-makeup-brush-set', 'name' => 'Professional Makeup Brush Set', 'category' => 'makeup', 'price' => 690, 'compare_price' => 820, 'stock' => 90, 'image' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=900&q=80', 'badge' => null],
            ['sku' => 'COS-0009', 'slug' => 'argan-repair-hair-oil', 'name' => 'Argan Repair Hair Oil', 'category' => 'hair-care', 'price' => 620, 'compare_price' => 750, 'stock' => 80, 'image' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=900&q=80', 'badge' => null],
            ['sku' => 'COS-0010', 'slug' => 'keratin-smooth-shampoo', 'name' => 'Keratin Smooth Shampoo', 'category' => 'hair-care', 'price' => 560, 'compare_price' => 680, 'stock' => 85, 'image' => 'https://images.unsplash.com/photo-1535585209827-a15fc2caeb2d?auto=format&fit=crop&w=900&q=80', 'badge' => null],
            ['sku' => 'COS-0011', 'slug' => 'leave-in-conditioner-spray', 'name' => 'Leave-In Conditioner Spray', 'category' => 'hair-care', 'price' => 590, 'compare_price' => 700, 'stock' => 60, 'image' => 'https://images.unsplash.com/photo-1527799820374-dcf8d9d4a388?auto=format&fit=crop&w=900&q=80', 'badge' => 'NEW'],
            ['sku' => 'COS-0012', 'slug' => 'floral-eau-de-parfum', 'name' => 'Floral Eau de Parfum', 'category' => 'fragrance', 'price' => 1250, 'compare_price' => 1500, 'stock' => 40, 'image' => 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=80', 'badge' => null],
            ['sku' => 'COS-0013', 'slug' => 'oud-perfume-oil', 'name' => 'Premium Oud Perfume Oil', 'category' => 'fragrance', 'price' => 980, 'compare_price' => 1200, 'stock' => 45, 'image' => 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=900&q=80', 'badge' => 'PREMIUM'],
            ['sku' => 'COS-0014', 'slug' => 'shea-butter-body-lotion', 'name' => 'Shea Butter Body Lotion', 'category' => 'bath-body', 'price' => 480, 'compare_price' => 560, 'stock' => 90, 'image' => 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=80', 'badge' => null],
            ['sku' => 'COS-0015', 'slug' => 'coffee-exfoliating-body-scrub', 'name' => 'Coffee Exfoliating Body Scrub', 'category' => 'bath-body', 'price' => 650, 'compare_price' => 780, 'stock' => 70, 'image' => 'https://images.unsplash.com/photo-1570172618214-df8f7d7b7e5f?auto=format&fit=crop&w=900&q=80', 'badge' => null],
        ];

        foreach ($products as $productData) {
            $product = Product::updateOrCreate(
                ['sku' => $productData['sku']],
                [
                    'category_id' => $categoryModels[$productData['category']]->id,
                    'brand_id' => $brand->id,
                    'name' => $productData['name'],
                    'name_bn' => $productData['name'],
                    'slug' => $productData['slug'],
                    'description' => 'Quality cosmetic product from the SkyBuy beauty collection.',
                    'type' => 'stock',
                    'status' => 'published',
                    'price' => $productData['price'],
                    'compare_price' => $productData['compare_price'],
                    'weight_kg' => .25,
                    'moq' => 1,
                    'primary_image' => $productData['image'],
                    'gallery' => [$productData['image']],
                    'attributes' => ['rating' => 5, 'sold' => '0', 'badge' => $productData['badge']],
                    'featured' => true,
                    'published_at' => now(),
                ],
            );

            $product->variants()->updateOrCreate(
                ['sku' => $productData['sku'].'-STD'],
                ['name' => 'Standard', 'price' => $productData['price'], 'stock' => $productData['stock'], 'is_active' => true, 'options' => ['pack' => 'Standard']],
            );
        }
    }
}
