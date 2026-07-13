<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class ClearCache
{
    public const HERO_BANNERS = 'hero_banners';
    public const CATEGORIES = 'hero_categories';
    public const FEATURED_PRODUCTS = 'hero_featured_products';
    public const LATEST_PRODUCTS = 'hero_latest_products';

    /**
     * Clear all homepage cache.
     */
    public static function clearBanner(): void
    {
        Cache::forget(self::HERO_BANNERS);
    }

    public static function clearCategory(): void
    {
        Cache::forget(self::CATEGORIES);
    }

    public static function clearProduct(): void
    {
        Cache::forget(self::LATEST_PRODUCTS);
        Cache::forget(self::FEATURED_PRODUCTS);
    }
        
    public static function clear(): void
    {
        Cache::forget(self::CATEGORIES);
        Cache::forget(self::HERO_BANNERS);
        Cache::forget(self::LATEST_PRODUCTS);
        Cache::forget(self::FEATURED_PRODUCTS);
    }

}