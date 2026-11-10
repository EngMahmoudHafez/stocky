<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogAttributes
{
    public const PRODUCT_FIELDS = ['material', 'season'];

    public const VARIANT_FIELDS = ['color', 'size'];

    public static function filters(Request $request): array
    {
        $filters = [];
        foreach ([...self::PRODUCT_FIELDS, ...self::VARIANT_FIELDS] as $field) {
            $value = $request->query($field);
            if (is_string($value) && trim($value) !== '' && mb_strlen($value) <= 100) {
                $filters[$field] = trim($value);
            }
        }

        return $filters;
    }

    public static function filterProducts(Builder $products, array $filters, ?array $availableWarehouses = null): void
    {
        self::applyFields($products, $filters, self::PRODUCT_FIELDS, 'products.');
        if (array_intersect(self::VARIANT_FIELDS, array_keys($filters))) {
            $products->whereHas('variants', function (Builder $variants) use ($filters, $availableWarehouses) {
                self::applyFields($variants->whereNull('product_variants.deleted_at'), $filters, self::VARIANT_FIELDS);
                if ($availableWarehouses !== null) {
                    $variants->whereExists(function ($stock) use ($availableWarehouses) {
                        $stock->from('product_warehouse')->whereColumn('product_variant_id', 'product_variants.id')
                            ->whereIn('warehouse_id', $availableWarehouses)->whereNull('deleted_at')->where('qte', '>', 0);
                    });
                }
            });
        }
    }

    public static function filterSales(Builder $lines, array $filters): void
    {
        if (array_intersect(self::PRODUCT_FIELDS, array_keys($filters))) {
            $lines->whereHas('product', fn (Builder $products) => self::applyFields($products, $filters, self::PRODUCT_FIELDS));
        }
        if (array_intersect(self::VARIANT_FIELDS, array_keys($filters))) {
            $lines->whereHas('variant', fn (Builder $variants) => self::applyFields($variants, $filters, self::VARIANT_FIELDS));
        }
    }

    public static function options(bool $storefront = false): array
    {
        $products = Product::whereNull('products.deleted_at');
        if ($storefront) {
            $products->where('products.is_active', 1)->where('products.hide_from_online_store', 0);
        }
        $variants = ProductVariant::whereNull('deleted_at')->whereIn('product_id', (clone $products)->select('id'));

        $options = [];
        foreach (self::PRODUCT_FIELDS as $field) {
            $options[$field] = self::distinctValues($products, $field);
        }
        foreach (self::VARIANT_FIELDS as $field) {
            $options[$field] = self::distinctValues($variants, $field);
        }

        return $options;
    }

    private static function applyFields(Builder $query, array $filters, array $fields, string $prefix = ''): void
    {
        foreach (array_intersect_key($filters, array_flip($fields)) as $field => $value) {
            $query->where($prefix.$field, $value);
        }
    }

    private static function distinctValues(Builder $query, string $field): array
    {
        return (clone $query)->whereNotNull($field)->where($field, '<>', '')
            ->distinct()->orderBy($field)->pluck($field)->all();
    }
}
