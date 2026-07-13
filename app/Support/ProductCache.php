<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class ProductCache
{
    public static function details(string $slug): string
    {
        return "product_details_{$slug}";
    }

    public static function related(int $productId): string
    {
        return "related_products_{$productId}";
    }

    public static function clear(string $slug, int $productId): void
    {
        Cache::forget(self::details($slug));
        Cache::forget(self::related($productId));
    }
}