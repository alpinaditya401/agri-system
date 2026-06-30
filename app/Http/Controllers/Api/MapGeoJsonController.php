<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Support\DashboardRegion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * GeoJSON API Controller
 *
 * Provides geographic data endpoints consumed by frontend map libraries
 * (Leaflet.js / Google Maps). All responses are GeoJSON FeatureCollection.
 *
 * Endpoints:
 *   GET /api/map/farmers        — Verified farmer locations + produce info
 *   GET /api/map/distributors   — Distributor locations + stock summary
 *   GET /api/map/products       — Active product listings with origin coords
 *   GET /api/map/combined       — All layers in one call
 */
class MapGeoJsonController extends Controller
{
    private int $cacheTtl = 1800; // 30 minutes
    private const INDONESIA_MIN_LAT = -11.2;
    private const INDONESIA_MAX_LAT = 6.5;
    private const INDONESIA_MIN_LNG = 94.5;
    private const INDONESIA_MAX_LNG = 141.2;

    /**
     * GeoJSON: verified farmer locations.
     * Publicly accessible (used on landing page map).
     */
    public function farmers(Request $request): JsonResponse
    {
        $regionFilters = DashboardRegion::fromRequest($request);
        $cacheKey = 'geojson_farmers_v3_' . $this->regionCacheSuffix($request);

        $geoJson = Cache::remember($cacheKey, $this->cacheTtl, function () use ($regionFilters) {
            $query = User::with(['farmerProfile', 'products' => fn($q) => $q->where('status', 'active')])
                ->whereHas('role', fn($q) => $q->where('name', 'farmer'))
                ->whereHas('farmerProfile', fn($q) => $q->where('verification_status', 'verified'))
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereBetween('latitude', [self::INDONESIA_MIN_LAT, self::INDONESIA_MAX_LAT])
                ->whereBetween('longitude', [self::INDONESIA_MIN_LNG, self::INDONESIA_MAX_LNG])
                ->where('is_active', true);

            DashboardRegion::applyUser($query, $regionFilters);

            $farmers = $query->get();

            return $this->buildFeatureCollection(
                $farmers->map(fn($farmer) => $this->farmerToFeature($farmer))
            );
        });

        return response()->json($geoJson)
            ->header('Cache-Control', 'public, max-age=1800')
            ->header('Content-Type', 'application/geo+json');
    }

    /**
     * GeoJSON: distributor locations with available fertilizer stock.
     */
    public function distributors(Request $request): JsonResponse
    {
        $regionFilters = DashboardRegion::fromRequest($request);
        $cacheKey = 'geojson_distributors_v3_' . $this->regionCacheSuffix($request);

        $geoJson = Cache::remember($cacheKey, $this->cacheTtl, function () use ($regionFilters) {
            $query = User::with([
                    'distributorProfile',
                    'fertilizerStocks.fertilizerType',
                ])
                ->whereHas('role', fn($q) => $q->where('name', 'distributor'))
                ->whereHas('distributorProfile', fn($q) => $q->where('verification_status', 'verified'))
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereBetween('latitude', [self::INDONESIA_MIN_LAT, self::INDONESIA_MAX_LAT])
                ->whereBetween('longitude', [self::INDONESIA_MIN_LNG, self::INDONESIA_MAX_LNG])
                ->where('is_active', true);

            DashboardRegion::applyUser($query, $regionFilters);

            $distributors = $query->get();

            return $this->buildFeatureCollection(
                $distributors->map(fn($d) => $this->distributorToFeature($d))
            );
        });

        return response()->json($geoJson)
            ->header('Content-Type', 'application/geo+json');
    }

    /**
     * GeoJSON: active product listings with their origin coordinates.
     * Useful for showing "what's available near me" on the map.
     */
    public function products(Request $request): JsonResponse
    {
        $regionFilters = DashboardRegion::fromRequest($request);
        $cacheKey = 'geojson_products_v3_' . md5(json_encode([
            'category' => $request->query('category', 'all'),
            'province' => $regionFilters['province'] ?? '',
            'district' => $regionFilters['district'] ?? '',
        ]));

        $geoJson = Cache::remember($cacheKey, 600, function () use ($request, $regionFilters) {
            $query = Product::with('farmer', 'category')
                ->where('status', 'active')
                ->where('stock_quantity', '>', 0)
                ->whereNotNull('origin_lat')
                ->whereNotNull('origin_lng')
                ->whereBetween('origin_lat', [self::INDONESIA_MIN_LAT, self::INDONESIA_MAX_LAT])
                ->whereBetween('origin_lng', [self::INDONESIA_MIN_LNG, self::INDONESIA_MAX_LNG]);

            if ($request->query('category')) {
                $query->whereHas('category', fn($q) => $q->where('slug', $request->query('category')));
            }

            DashboardRegion::applyProduct($query, $regionFilters);

            $products = $query->get();

            return $this->buildFeatureCollection(
                $products->map(fn($p) => $this->productToFeature($p))
            );
        });

        return response()->json($geoJson)
            ->header('Content-Type', 'application/geo+json');
    }

    /**
     * Combined GeoJSON: all layers merged.
     * Frontend can filter by feature.properties.layer.
     */
    public function combined(Request $request): JsonResponse
    {
        $cacheKey = 'geojson_combined_v3_' . $this->regionCacheSuffix($request);

        $geoJson = Cache::remember($cacheKey, 900, function () use ($request) {
            $farmerFeatures = $this->farmers($request)->original['features'] ?? [];
            $distributorFeatures = $this->distributors($request)->original['features'] ?? [];
            $productFeatures = $this->products($request)->original['features'] ?? [];

            return $this->buildFeatureCollection(
                array_merge($farmerFeatures, $distributorFeatures, $productFeatures)
            );
        });

        return response()->json($geoJson)->header('Content-Type', 'application/geo+json');
    }

    // ── GeoJSON Feature Builders ─────────────────────────────────────────────

    private function farmerToFeature(User $farmer): array
    {
        $commodityKey = $this->commodityImageKey($farmer->farmerProfile?->main_commodity);

        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float) $farmer->longitude, (float) $farmer->latitude],
            ],
            'properties' => [
                'layer' => 'farmer',
                'id' => $farmer->id,
                'name' => $farmer->name,
                'village' => $farmer->village,
                'district' => $farmer->district,
                'province' => $farmer->province,
                'main_commodity' => $farmer->farmerProfile?->main_commodity,
                'commodity_key' => $commodityKey,
                'image' => $this->commodityImageUrl($commodityKey),
                'land_area_ha' => $farmer->farmerProfile?->land_area_hectares,
                'farmer_group' => $farmer->farmerProfile?->farmer_group_name,
                'active_products' => $farmer->products->count(),
                'products' => $farmer->products->take(3)->map(fn($product) => [
                    'name' => $product->name,
                    'stock' => $product->stock_quantity,
                    'unit' => $product->unit,
                    'price' => $product->price_per_unit,
                    'url' => route('products.show', $product->slug),
                ])->values(),
                'marker_icon' => 'farmer',
            ],
        ];
    }

    private function distributorToFeature(User $distributor): array
    {
        $stockSummary = $distributor->fertilizerStocks->map(fn($s) => [
            'type' => $s->fertilizerType?->name,
            'available_kg' => max(0, $s->stock_kg - $s->reserved_kg),
        ])->filter(fn($s) => $s['available_kg'] > 0)->values();

        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float) $distributor->longitude, (float) $distributor->latitude],
            ],
            'properties' => [
                'layer' => 'distributor',
                'id' => $distributor->id,
                'name' => $distributor->name,
                'company_name' => $distributor->distributorProfile?->company_name,
                'district' => $distributor->district,
                'province' => $distributor->province,
                'stock' => $stockSummary,
                'has_stock' => $stockSummary->isNotEmpty(),
                'marker_icon' => 'distributor',
            ],
        ];
    }

    private function productToFeature(Product $product): array
    {
        $commodityKey = $this->commodityImageKey($product->name);

        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float) $product->origin_lng, (float) $product->origin_lat],
            ],
            'properties' => [
                'layer' => 'product',
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price_per_unit,
                'unit' => $product->unit,
                'stock' => $product->stock_quantity,
                'category' => $product->category?->name,
                'commodity_key' => $commodityKey,
                'farmer_name' => $product->farmer?->name,
                'district' => $product->origin_district,
                'province' => $product->origin_province,
                'url' => route('products.show', $product->slug),
                'image' => $this->productImageUrl($product, $commodityKey),
                'marker_icon' => 'product',
            ],
        ];
    }

    private function productImageUrl(Product $product, string $commodityKey): string
    {
        if ($product->main_image_url) {
            return $product->main_image_url;
        }

        return $this->commodityImageUrl($commodityKey);
    }

    private function commodityImageUrl(?string $commodityKey): string
    {
        $key = $commodityKey ?: 'placeholder';

        foreach (['webp', 'jpg', 'jpeg', 'png', 'svg'] as $extension) {
            $path = "images/commodities/{$key}.{$extension}";

            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }

        return asset('images/commodities/placeholder.svg');
    }

    private function commodityImageKey(?string $value): string
    {
        $name = Str::lower((string) $value);

        return match (true) {
            Str::contains($name, ['cabai', 'cabe', 'chili']) => 'cabai-merah',
            Str::contains($name, ['bawang merah']) => 'bawang-merah',
            Str::contains($name, ['bawang']) => 'bawang',
            Str::contains($name, ['kangkung']) => 'kangkung',
            Str::contains($name, ['beras']) => 'beras',
            Str::contains($name, ['gabah', 'padi']) => 'gabah',
            Str::contains($name, ['tomat']) => 'tomat',
            Str::contains($name, ['sawi']) => 'sawi',
            Str::contains($name, ['bayam']) => 'bayam',
            Str::contains($name, ['wortel']) => 'wortel',
            Str::contains($name, ['kentang']) => 'kentang',
            Str::contains($name, ['kubis', 'kol']) => 'kubis',
            default => Str::slug((string) $value) ?: 'placeholder',
        };
    }

    private function buildFeatureCollection(iterable $features): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => array_values(is_array($features) ? $features : $features->toArray()),
        ];
    }

    private function regionCacheSuffix(Request $request): string
    {
        return md5(json_encode([
            'province' => $request->query('province', ''),
            'district' => $request->query('district', ''),
        ]));
    }
}