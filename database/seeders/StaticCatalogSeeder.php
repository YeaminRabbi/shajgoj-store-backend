<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaticCatalogSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            ['Bags', 'bags', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80'],
            ['Jewelry', 'jewelry', 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=600&q=80'],
            ['Shoes', 'shoes', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80'],
            ['Beauty', 'beauty-products', 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=600&q=80'],
            ['Mens Wear', 'clothing-men', 'https://images.unsplash.com/photo-1485230895905-ec40ba36b9bc?auto=format&fit=crop&w=600&q=80'],
            ['Women Wear', 'womens-clothing', 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=600&q=80'],
            ['Eyewear', 'eyewear', 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=600&q=80'],
            ['Baby Items', 'baby-items', 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&w=600&q=80'],
            ['Watches', 'watches', 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=600&q=80'],
            ['Gadgets', 'gadgets', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=600&q=80'],
        ];

        $categoryModels = collect($categories)->mapWithKeys(function (array $category, int $index): array {
            $model = Category::withTrashed()->updateOrCreate(
                ['slug' => $category[1]],
                ['name' => $category[0], 'image' => $category[2], 'sort_order' => $index, 'is_active' => true],
            );

            return [$category[1] => $model];
        });

        $brand = Brand::withTrashed()->updateOrCreate(
            ['slug' => 'sky-select'],
            ['name' => 'Sky Select', 'name_bn' => 'স্কাই সিলেক্ট', 'status' => 'approved'],
        );

        $products = [
            ['abb-06596743730002', 'Longxiang energy dumpling bag with nylon shoulder strap', 'bags', 3933, 4139, '234', 'photo-1584917865442-de89df76afd3', 'NEW'],
            ['abb-0402575717978', 'Vintage monogram drawstring bucket bag for women', 'bags', 3911, 4116, '104', 'photo-1548036328-c9fa89d128fa', null],
            ['abb-0042041127653', 'Floral fabric shoulder bag with coin purse', 'bags', 110, 115, '1k', 'photo-1566150905458-1bf1fc113f0d', null],
            ['sh-2026-runner', '2026 lightweight fashion runner sneakers', 'shoes', 1450, 1600, '531', 'photo-1549298916-b41d501d3772', 'HOT'],
            ['jw-gold-102', 'Minimal gold-plated layered necklace set', 'jewelry', 489, 549, '760', 'photo-1599643478518-a784e5dc4c8f', null],
            ['wt-smart-220', 'Smart fitness watch with AMOLED display', 'watches', 2180, 2500, '321', 'photo-1508685096489-7aacd43bd3b1', 'NEW'],
            ['gd-audio-88', 'Wireless noise-cancelling earbuds', 'gadgets', 721, 850, '932', 'photo-1606220945770-b5b6c2c55bf1', null],
            ['bw-skin-12', 'Daily glow skincare essentials gift set', 'beauty-products', 639, 720, '402', 'photo-1556228578-8c89e6adf883', null],
            ['wm-dress-1', 'Summer floral midi dress for women', 'womens-clothing', 1280, 1400, '187', 'photo-1496747611176-843222e1e57c', null],
            ['mn-linen-8', 'Premium casual linen shirt', 'clothing-men', 980, 1100, '210', 'photo-1603252109303-2751441dd157', null],
            ['ey-classic-7', 'Classic UV-protection sunglasses', 'eyewear', 348, 420, '452', 'photo-1511499767150-a48a237f0083', null],
            ['bb-soft-20', 'Soft learning toy collection for kids', 'baby-items', 662, 750, '116', 'photo-1559454403-b8fb88521f11', null],
        ];

        foreach ($products as [$id, $name, $category, $price, $comparePrice, $sold, $photo, $badge]) {
            $sku = 'WEB-'.strtoupper($id);
            $image = "https://images.unsplash.com/$photo?auto=format&fit=crop&w=900&q=80";
            $moq = str_starts_with($id, 'abb-') ? 1 : 10;

            $product = Product::withTrashed()->updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $categoryModels[$category]->id,
                    'brand_id' => $brand->id,
                    'name' => $name,
                    'name_bn' => $name,
                    'slug' => $id,
                    'description' => 'Marketplace product managed through the SkyBuy catalog.',
                    'type' => 'sourcing',
                    'status' => 'published',
                    'price' => $price,
                    'compare_price' => $comparePrice,
                    'weight_kg' => .5,
                    'moq' => $moq,
                    'primary_image' => $image,
                    'gallery' => [$image],
                    'attributes' => ['rating' => 5, 'sold' => $sold, 'badge' => $badge],
                    'tier_prices' => [['min' => $moq, 'price' => $price], ['min' => 50, 'price' => round($price * .95)], ['min' => 200, 'price' => round($price * .89)]],
                    'featured' => true,
                    'published_at' => now(),
                ],
            );

            $product->variants()->withTrashed()->updateOrCreate(
                ['sku' => "$sku-STD"],
                ['name' => 'Standard', 'price' => $price, 'stock' => 9999, 'is_active' => true, 'options' => ['pack' => 'Standard']],
            );
        }
    }
}
