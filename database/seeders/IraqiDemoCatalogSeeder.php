<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\SizeGuide;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IraqiDemoCatalogSeeder extends Seeder
{
    public function run(int $ownerId, array $warehouseIds, array $clientIds): void
    {
        $unitId = DB::table('units')->insertGetId(['name' => 'قطعة', 'ShortName' => 'قطعة']);
        $brandId = DB::table('brands')->insertGetId(['name' => 'براند العراقي']);
        $collections = $this->createCollections();
        $guides = $this->createSizeGuides();
        $categories = [];
        $stock = [];

        foreach ($this->styles() as [$code, $name, $category, $collection, $cost, $price, $colors]) {
            $categoryId = $categories[$category] ??= DB::table('categories')->insertGetId([
                'code' => 'IQ-C'.(count($categories) + 1), 'name' => $category,
            ]);
            $product = [
                'code' => 'IQ-'.$code, 'name' => $name, 'category_id' => $categoryId, 'brand_id' => $brandId,
                'cost' => $cost, 'price' => $price, 'unit_id' => $unitId, 'size_guide_id' => $guides[$category],
            ];
            $variants = $this->createStyle($product, $colors);
            $collections[$collection]->products()->attach($variants[0]['product_id']);
            $stock = [...$stock, ...$variants];
        }

        $this->callWith(IraqiDemoOperationsSeeder::class, compact('ownerId', 'warehouseIds', 'clientIds', 'stock'));
    }

    private function styles(): array
    {
        return [
            ['TEE', 'تيشيرت قطن بيسك', 'تيشيرتات', 'everyday', 170, 349, ['BLK' => 'أسود', 'CRM' => 'أوف وايت']],
            ['SHT', 'قميص أوكسفورد', 'قمصان', 'everyday', 260, 579, ['BLU' => 'أزرق فاتح', 'WHT' => 'أبيض']],
            ['CHN', 'بنطلون تشينو', 'بناطيل', 'everyday', 315, 649, ['SND' => 'بيج', 'NVY' => 'كحلي']],
            ['HDY', 'هودي قطن بسوستة', 'هوديز', 'winter', 330, 749, ['GRN' => 'زيتي', 'GRY' => 'رمادي']],
            ['JKT', 'جاكيت جينز', 'جواكت', 'winter', 475, 999, ['IND' => 'أزرق جينز', 'CHR' => 'فحمي']],
        ];
    }

    private function createCollections(): array
    {
        return [
            'everyday' => Collection::create([
                'slug' => 'everyday-essentials', 'title' => 'أساسيات كل يوم',
                'description' => 'قطع مريحة وسهلة التنسيق للمشاوير اليومية.', 'sort_order' => 1,
            ]),
            'winter' => Collection::create([
                'slug' => 'winter-layers', 'title' => 'كولكشن خريف وشتاء',
                'description' => 'هوديز وجواكت للجو البارد.', 'sort_order' => 2,
            ]),
        ];
    }

    private function createSizeGuides(): array
    {
        $tops = SizeGuide::create([
            'name' => 'القطع العلوية • مقاسات للعرض',
            'columns' => ['المقاس', 'محيط الصدر (سم)', 'طول القطعة (سم)'],
            'rows' => [['S', '88–92', 68], ['M', '93–97', 70], ['L', '98–103', 72], ['XL', '104–110', 74]],
        ]);
        $pants = SizeGuide::create([
            'name' => 'البناطيل • مقاسات للعرض',
            'columns' => ['المقاس', 'محيط الخصر (سم)', 'طول الساق الداخلي (سم)'],
            'rows' => [['S', '74–78', 76], ['M', '79–83', 77], ['L', '84–89', 78], ['XL', '90–96', 79]],
        ]);

        return array_fill_keys(['تيشيرتات', 'قمصان', 'هوديز', 'جواكت'], $tops->id) + ['بناطيل' => $pants->id];
    }

    private function createStyle(array $product, array $colors): array
    {
        $cover = $this->imageFilename($product['code'], array_key_first($colors));
        $productId = DB::table('products')->insertGetId($product + [
            'type' => 'is_variant', 'Type_barcode' => 'CODE128', 'is_variant' => 1,
            'unit_sale_id' => $product['unit_id'], 'unit_purchase_id' => $product['unit_id'],
            'wholesale_price' => $product['price'] - 50, 'min_price' => $product['price'] - 75,
            'image' => $cover, 'stock_alert' => 5, 'is_featured' => 1, 'hide_from_online_store' => 0,
            'note' => 'متاح بأكثر من لون ومقاس. راجع دليل المقاسات قبل الطلب.',
        ]);

        return $this->createVariants($productId, $product, $colors);
    }

    private function createVariants(int $productId, array $product, array $colors): array
    {
        $variants = [];
        foreach ($colors as $colorCode => $color) {
            $filename = $this->imageFilename($product['code'], $colorCode);
            DB::table('product_images')->insert([
                'product_id' => $productId, 'image_path' => $filename,
                'is_main' => $colorCode === array_key_first($colors), 'sort_order' => count($variants),
            ]);
            foreach (['S', 'M', 'L', 'XL'] as $size) {
                $variantId = DB::table('product_variants')->insertGetId([
                    'product_id' => $productId, 'name' => $color.' / '.$size, 'image' => $filename,
                    'code' => $product['code'].'-'.$colorCode.'-'.$size,
                    'cost' => $product['cost'], 'price' => $product['price'],
                    'wholesale' => $product['price'] - 50, 'min_price' => $product['price'] - 75,
                ]);
                $variants[] = ['product_id' => $productId, 'product_variant_id' => $variantId] + $product;
            }
        }

        return $variants;
    }

    private function imageFilename(string $productCode, string $colorCode): string
    {
        return strtolower($productCode.'-'.$colorCode).'.svg';
    }
}
