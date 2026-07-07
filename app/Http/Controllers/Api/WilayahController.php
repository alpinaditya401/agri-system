<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WilayahController extends Controller
{
    private const BASE_URL = 'https://wilayah.id/api';

    public function provinces(): JsonResponse
    {
        return $this->proxy('provinces.json', 'wilayah.provinces');
    }

    public function regencies(string $provinceCode): JsonResponse
    {
        return $this->proxy("regencies/{$provinceCode}.json", "wilayah.regencies.{$provinceCode}");
    }

    public function districts(string $regencyCode): JsonResponse
    {
        return $this->proxy("districts/{$regencyCode}.json", "wilayah.districts.{$regencyCode}");
    }

    public function villages(string $districtCode): JsonResponse
    {
        return $this->proxy("villages/{$districtCode}.json", "wilayah.villages.{$districtCode}");
    }

    private function proxy(string $path, string $cacheKey): JsonResponse
    {
        try {
            $payload = Cache::remember($cacheKey, now()->addDay(), function () use ($path) {
                $response = Http::acceptJson()
                    ->timeout(15)
                    ->retry(2, 250)
                    ->get(self::BASE_URL . '/' . ltrim($path, '/'));

                if (! $response->successful()) {
                    throw new \RuntimeException('Wilayah API returned HTTP ' . $response->status());
                }

                return $response->json();
            });

            return response()->json([
                'data' => collect($payload['data'] ?? [])
                    ->map(fn ($item) => [
                        'code' => (string) ($item['code'] ?? ''),
                        'name' => (string) ($item['name'] ?? ''),
                    ])
                    ->filter(fn ($item) => $item['code'] !== '' && $item['name'] !== '')
                    ->values(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Wilayah proxy failed', [
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Data wilayah belum bisa dimuat.',
                'data' => [],
            ], 502);
        }
    }
}
