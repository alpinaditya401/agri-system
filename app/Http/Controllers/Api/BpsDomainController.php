<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BpsDomainController extends Controller
{
    public function provinces(): JsonResponse
    {
        return response()->json([
            'source' => 'BPS WebAPI /domain',
            'data' => $this->domains('prov'),
        ]);
    }

    public function regencies(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province' => ['required', 'string', 'regex:/^[0-9]{4}$/'],
        ]);

        $provinceCode = $validated['province'];
        $provincePrefix = substr($provinceCode, 0, 2);

        $regencies = collect($this->domains('kab'))
            ->filter(fn(array $domain) => Str::startsWith($domain['code'], $provincePrefix))
            ->values()
            ->all();

        return response()->json([
            'source' => 'BPS WebAPI /domain',
            'data' => $regencies,
        ]);
    }

    private function domains(string $type): array
    {
        $apiKey = (string) config('services.bps.api_key');

        if ($apiKey === '') {
            abort(response()->json([
                'message' => 'BPS_API_KEY belum dikonfigurasi.',
            ], 503));
        }

        return Cache::store('file')->remember("bps_domains_{$type}", now()->addDay(), function () use ($type, $apiKey) {
            $baseUrl = rtrim((string) config('services.bps.base_url'), '/');
            $timeout = (int) config('services.bps.timeout', 30);

            $response = Http::timeout($timeout)
                ->acceptJson()
                ->get("{$baseUrl}/domain/type/{$type}/key/{$apiKey}");

            if (! $response->successful()) {
                abort(response()->json([
                    'message' => 'BPS WebAPI domain belum bisa diakses.',
                ], 502));
            }

            $json = $response->json();

            if (($json['status'] ?? null) !== 'OK') {
                abort(response()->json([
                    'message' => $json['message'] ?? 'BPS WebAPI domain mengembalikan error.',
                ], 502));
            }

            return collect($json['data'][1] ?? [])
                ->map(fn(array $domain) => [
                    'code' => (string) ($domain['domain_id'] ?? ''),
                    'name' => (string) ($domain['domain_name'] ?? ''),
                    'url' => $domain['domain_url'] ?? null,
                ])
                ->filter(fn(array $domain) => $domain['code'] !== '' && $domain['name'] !== '')
                ->values()
                ->all();
        });
    }
}
