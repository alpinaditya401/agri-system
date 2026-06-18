<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\FertilizerStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

    /**
     * GeoJSON: verified farmer locations.
     * Publicly accessible (used on landing page map).
     */
    public function farmers(Request $request): JsonResponse
    {
        $cacheKey = 'geojson_farmers_' . ($request->query('district', 'all'));

        $geoJson = Cache::remember($cacheKey, $this->cacheTtl, function () use ($request) {
            $query = User::with(['farmerProfile', 'products' => fn($q) => $q->where('status', 'active')])
                ->whereHas('role', fn($q) => $q->where('name', 'farmer'))
                ->whereHas('farmerProfile', fn($q) => $q->where('verification_status', 'verified'))
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('is_active', true);

            if ($request->query('district')) {
                $query->where('district', $request->query('district'));
            }

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
    public function distributors(): JsonResponse
    {
        $geoJson = Cache::remember('geojson_distributors', $this->cacheTtl, function () {
            $distributors = User::with([
                    'distributorProfile',
                    'fertilizerStocks.fertilizerType',
                ])
                ->whereHas('role', fn($q) => $q->where('name', 'distributor'))
                ->whereHas('distributorProfile', fn($q) => $q->where('verification_status', 'verified'))
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('is_active', true)
                ->get();

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
        $cacheKey = 'geojson_products_' . ($request->query('category', 'all'));

        $geoJson = Cache::remember($cacheKey, 600, function () use ($request) {
            $query = Product::with('farmer', 'category')
                ->where('status', 'active')
                ->where('stock_quantity', '>', 0)
                ->whereNotNull('origin_lat')
                ->whereNotNull('origin_lng');

            if ($request->query('category')) {
                $query->whereHas('category', fn($q) => $q->where('slug', $request->query('category')));
            }

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
    public function combined(): JsonResponse
    {
        $geoJson = Cache::remember('geojson_combined', 900, function () {
            $farmerFeatures     = $this->farmers(new Request())->original['features'] ?? [];
            $distributorFeatures = $this->distributors()->original['features'] ?? [];
            $productFeatures    = $this->products(new Request())->original['features'] ?? [];

            return $this->buildFeatureCollection(
                array_merge($farmerFeatures, $distributorFeatures, $productFeatures)
            );
        });

        return response()->json($geoJson)->header('Content-Type', 'application/geo+json');
    }

    // ── GeoJSON Feature Builders ─────────────────────────────────────────────

    private function farmerToFeature(User $farmer): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type'        => 'Point',
                'coordinates' => [(float) $farmer->longitude, (float) $farmer->latitude],
            ],
            'properties' => [
                'layer'            => 'farmer',
                'id'               => $farmer->id,
                'name'             => $farmer->name,
                'village'          => $farmer->village,
                'district'         => $farmer->district,
                'province'         => $farmer->province,
                'main_commodity'   => $farmer->farmerProfile?->main_commodity,
                'land_area_ha'     => $farmer->farmerProfile?->land_area_hectares,
                'farmer_group'     => $farmer->farmerProfile?->farmer_group_name,
                'active_products'  => $farmer->products->count(),
                'marker_icon'      => 'farmer',
            ],
        ];
    }

    private function distributorToFeature(User $distributor): array
    {
        $stockSummary = $distributor->fertilizerStocks->map(fn($s) => [
            'type'         => $s->fertilizerType?->name,
            'available_kg' => max(0, $s->stock_kg - $s->reserved_kg),
        ])->filter(fn($s) => $s['available_kg'] > 0)->values();

        return [
            'type' => 'Feature',
            'geometry' => [
                'type'        => 'Point',
                'coordinates' => [(float) $distributor->longitude, (float) $distributor->latitude],
            ],
            'properties' => [
                'layer'        => 'distributor',
                'id'           => $distributor->id,
                'name'         => $distributor->name,
                'company_name' => $distributor->distributorProfile?->company_name,
                'district'     => $distributor->district,
                'province'     => $distributor->province,
                'stock'        => $stockSummary,
                'has_stock'    => $stockSummary->isNotEmpty(),
                'marker_icon'  => 'distributor',
            ],
        ];
    }

    private function productToFeature(Product $product): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type'        => 'Point',
                'coordinates' => [(float) $product->origin_lng, (float) $product->origin_lat],
            ],
            'properties' => [
                'layer'         => 'product',
                'id'            => $product->id,
                'name'          => $product->name,
                'price'         => $product->price_per_unit,
                'unit'          => $product->unit,
                'stock'         => $product->stock_quantity,
                'category'      => $product->category?->name,
                'farmer_name'   => $product->farmer?->name,
                'district'      => $product->origin_district,
                'province'      => $product->origin_province,
                'image'         => $product->main_image ? asset('storage/' . $product->main_image) : null,
                'marker_icon'   => 'product',
            ],
        ];
    }

    private function buildFeatureCollection(iterable $features): array
    {
        return [
            'type'     => 'FeatureCollection',
            'features' => array_values(is_array($features) ? $features : $features->toArray()),
        ];
    }
}
