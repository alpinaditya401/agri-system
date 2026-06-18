<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BpsApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommodityPriceController extends Controller
{
    public function __construct(private readonly BpsApiService $bpsService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $limit = (int) $request->query('limit', 20);

        if ($category) {
            $prices = $this->bpsService->getByCategory($category, $limit);
        } else {
            $prices = $this->bpsService->getLatestPrices($limit);
        }

        return response()->json([
            'data' => $prices,
            'meta' => [
                'fetched_at' => now()->toDateTimeString(),
                'source'     => 'BPS (cached)',
            ],
        ]);
    }
}
