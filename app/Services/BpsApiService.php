<?php

namespace App\Services;

use App\Models\CommodityPrice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BpsApiService
{
    /**
     * Get the latest commodity prices from the local cache (commodity_prices table).
     * Data is populated by the FetchBpsDataCommand cron job.
     */
    public function getLatestPrices(int $limit = 20): array
    {
        try {
            return DB::table('commodity_prices')
                ->orderBy('price_date', 'desc')
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get prices grouped by category.
     */
    public function getByCategory(string $category, int $limit = 10): array
    {
        try {
            return DB::table('commodity_prices')
                ->where('category', $category)
                ->orderBy('price_date', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get price trend for a specific commodity over the last N days.
     */
    public function getPriceTrend(string $commodityName, int $days = 30): array
    {
        try {
            return DB::table('commodity_prices')
                ->where('commodity_name', $commodityName)
                ->where('price_date', '>=', now()->subDays($days))
                ->orderBy('price_date')
                ->get(['price_date', 'price', 'region'])
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get cached prices for the landing page (with file caching).
     */
    public function getCachedLandingPrices(): array
    {
        return Cache::remember('bps_landing_prices', 3600, function () {
            return $this->getLatestPrices(12);
        });
    }
}
