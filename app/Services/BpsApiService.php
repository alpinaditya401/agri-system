<?php

namespace App\Services;

use App\Models\CommodityPrice;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BpsApiService
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;
    private string $domain;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.bps.base_url'), '/');
        $this->apiKey = (string) config('services.bps.api_key');
        $this->timeout = (int) config('services.bps.timeout', 30);
        $this->domain = (string) config('services.bps.domain', '0000');
    }

    public function getLatestPrices(int $limit = 20): array
    {
        try {
            return CommodityPrice::query()
                ->whereIn('id', function ($query) {
                    $query->selectRaw('MAX(id)')
                        ->from('commodity_prices')
                        ->groupBy('commodity_code', 'region_code');
                })
                ->orderBy('price_date', 'desc')
                ->orderBy('commodity_name')
                ->limit($limit)
                ->get()
                ->map(fn($price) => (object) $price->toArray())
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    public function getByCategory(string $category, int $limit = 10): array
    {
        try {
            return CommodityPrice::query()
                ->where('category', $category)
                ->latest('price_date')
                ->limit($limit)
                ->get()
                ->map(fn($price) => (object) $price->toArray())
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    public function getPriceTrend(string $commodityName, int $days = 30): array
    {
        try {
            return CommodityPrice::query()
                ->where('commodity_name', $commodityName)
                ->where('price_date', '>=', now()->subDays($days))
                ->orderBy('price_date')
                ->get(['price_date', 'price', 'region'])
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    public function getCachedLandingPrices(): array
    {
        return Cache::remember('bps_landing_prices', 3600, fn() => $this->getLatestPrices(12));
    }

    public function fetchAndCacheAll(): array
    {
        if ($this->apiKey === '') {
            throw new \RuntimeException('BPS_API_KEY belum dikonfigurasi.');
        }

        $fetched = 0;
        $failed = 0;
        $errors = [];

        foreach ($this->directPriceSeries() as $series) {
            try {
                $record = $this->fetchLatestDirectPrice($series);
                $record ? $fetched += $this->storePrice($record) : $failed++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = $series['name'] . ': ' . $e->getMessage();
            }
        }

        foreach ($this->derivedSusenasSeries() as $series) {
            try {
                $record = $this->fetchDerivedSusenasPrice($series);
                $record ? $fetched += $this->storePrice($record) : $failed++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = $series['name'] . ': ' . $e->getMessage();
            }
        }

        Cache::forget('bps_landing_prices');

        return [
            'fetched' => $fetched,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    public function getProductReferencePrices(iterable $products): Collection
    {
        $latest = collect($this->getLatestPrices(100));

        return collect($products)->mapWithKeys(function (Product $product) use ($latest) {
            $match = $this->matchProductToCommodityCode($product);

            if (! $match) {
                return [$product->id => null];
            }

            $price = $latest->firstWhere('commodity_code', $match);

            return [$product->id => $price ? (object) $price : null];
        });
    }

    public function matchProductToCommodityCode(Product $product): ?string
    {
        $name = Str::lower($product->name . ' ' . ($product->category?->name ?? ''));

        $aliases = [
            'bps-gabah-gkp-petani' => ['gabah', 'padi', 'gkp'],
            'bps-beras-medium-penggilingan' => ['beras medium', 'beras'],
            'bps-cabai-rawit-susenas' => ['cabai rawit', 'cabe rawit'],
            'bps-cabai-merah-susenas' => ['cabai merah', 'cabe merah', 'cabai', 'cabe'],
            'bps-bawang-merah-susenas' => ['bawang merah'],
            'bps-sayur-susenas' => ['sayur', 'bayam', 'kangkung', 'sawi', 'tomat', 'wortel'],
            'bps-buah-susenas' => ['buah', 'mangga', 'pisang', 'jeruk', 'apel'],
        ];

        foreach ($aliases as $code => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($name, $keyword)) {
                    return $code;
                }
            }
        }

        return null;
    }

    private function directPriceSeries(): array
    {
        return [
            [
                'code' => 'bps-gabah-gkp-petani',
                'name' => 'Gabah Kering Panen (GKP) Tingkat Petani',
                'category' => 'Tanaman Pangan',
                'unit' => 'kg',
                'var' => 1034,
                'vervar' => 1,
                'turvar' => 0,
                'source_note' => 'Tabel Statistik BPS harga gabah bulanan tingkat petani.',
            ],
            [
                'code' => 'bps-gabah-gkg-petani',
                'name' => 'Gabah Kering Giling (GKG) Tingkat Petani',
                'category' => 'Tanaman Pangan',
                'unit' => 'kg',
                'var' => 1034,
                'vervar' => 2,
                'turvar' => 0,
                'source_note' => 'Tabel Statistik BPS harga gabah bulanan tingkat petani.',
            ],
            [
                'code' => 'bps-beras-premium-penggilingan',
                'name' => 'Beras Premium Tingkat Penggilingan',
                'category' => 'Pangan',
                'unit' => 'kg',
                'var' => 2277,
                'vervar' => 1,
                'turvar' => 0,
                'source_note' => 'Tabel Statistik BPS harga beras bulanan tingkat penggilingan.',
            ],
            [
                'code' => 'bps-beras-medium-penggilingan',
                'name' => 'Beras Medium Tingkat Penggilingan',
                'category' => 'Pangan',
                'unit' => 'kg',
                'var' => 2277,
                'vervar' => 2,
                'turvar' => 0,
                'source_note' => 'Tabel Statistik BPS harga beras bulanan tingkat penggilingan.',
            ],
        ];
    }

    private function derivedSusenasSeries(): array
    {
        return [
            [
                'code' => 'bps-sayur-susenas',
                'name' => 'Sayur-sayuran',
                'category' => 'Hortikultura',
                'unit' => 'kg',
                'quantity_var' => 2100,
                'expense_var' => 2116,
                'items' => [1806, 1807, 1808, 1810, 1811, 1812, 1813, 1814],
                'source_note' => 'Estimasi harga implisit BPS dari pengeluaran/konsumsi Susenas 2024.',
            ],
            [
                'code' => 'bps-buah-susenas',
                'name' => 'Buah-buahan',
                'category' => 'Hortikultura',
                'unit' => 'kg',
                'quantity_var' => 2102,
                'expense_var' => 2118,
                'items' => [1840, 1841, 1847, 1848, 1851, 1852],
                'source_note' => 'Estimasi harga implisit BPS dari pengeluaran/konsumsi Susenas 2024.',
            ],
        ];
    }

    private function fetchLatestDirectPrice(array $series): ?array
    {
        foreach ($this->availableYears((int) $series['var']) as $year) {
            if ((int) $year['th'] < now()->year - 5) {
                continue;
            }

            foreach (array_reverse($this->availablePeriods((int) $series['var'])) as $period) {
                $data = $this->fetchData([
                    'var' => $series['var'],
                    'vervar' => $series['vervar'],
                    'turvar' => $series['turvar'],
                    'th' => $year['th_id'],
                    'turth' => $period['turth_id'],
                ]);

                $value = $this->firstNumericValue($data['datacontent'] ?? []);

                if ($value !== null && $value > 0) {
                    return [
                        'commodity_name' => $series['name'],
                        'commodity_code' => $series['code'],
                        'category' => $series['category'],
                        'price' => $value,
                        'unit' => $series['unit'],
                        'region' => 'Nasional',
                        'region_code' => $this->domain,
                        'source' => 'BPS',
                        'price_date' => $this->periodDate((int) $year['th'], (int) $period['turth_id']),
                        'raw_data' => [
                            'method' => 'bps_direct',
                            'series' => $series,
                            'year' => $year,
                            'period' => $period,
                            'source_tables' => $this->sourceTablesFor($series['code']),
                            'source_note' => $series['source_note'],
                        ],
                    ];
                }
            }
        }

        return null;
    }

    private function fetchDerivedSusenasPrice(array $series): ?array
    {
        $year = collect($this->availableYears((int) $series['quantity_var']))
            ->first(fn($item) => (int) $item['th'] >= now()->year - 5);

        if (! $year) {
            return null;
        }

        $totalExpense = 0.0;
        $totalQuantity = 0.0;
        $usedItems = 0;

        foreach ($series['items'] as $turvar) {
            $quantityData = $this->fetchData([
                'var' => $series['quantity_var'],
                'turvar' => $turvar,
                'th' => $year['th_id'],
                'turth' => 0,
            ]);
            $expenseData = $this->fetchData([
                'var' => $series['expense_var'],
                'turvar' => $turvar,
                'th' => $year['th_id'],
                'turth' => 0,
            ]);

            $quantity = array_sum(array_filter(array_map('floatval', (array) ($quantityData['datacontent'] ?? [])), fn($value) => $value > 0));
            $expense = array_sum(array_filter(array_map('floatval', (array) ($expenseData['datacontent'] ?? [])), fn($value) => $value > 0));

            if ($quantity > 0 && $expense > 0) {
                $totalQuantity += $quantity;
                $totalExpense += $expense;
                $usedItems++;
            }
        }

        if ($totalQuantity <= 0 || $totalExpense <= 0) {
            return null;
        }

        return [
            'commodity_name' => $series['name'],
            'commodity_code' => $series['code'],
            'category' => $series['category'],
            'price' => round($totalExpense / $totalQuantity, 2),
            'unit' => $series['unit'],
            'region' => 'Nasional',
            'region_code' => $this->domain,
            'source' => 'BPS Susenas',
            'price_date' => Carbon::create((int) $year['th'], 12, 31)->toDateString(),
            'raw_data' => [
                'method' => 'bps_derived_susenas',
                'series' => $series,
                'year' => $year,
                'used_items' => $usedItems,
                'source_tables' => $this->sourceTablesFor($series['code']),
                'source_note' => $series['source_note'],
            ],
        ];
    }

    private function sourceTablesFor(string $commodityCode): array
    {
        $tables = config('bps_sources.tables', []);
        $keys = config("bps_sources.commodities.{$commodityCode}", []);

        return collect($keys)
            ->map(fn(string $key) => $tables[$key] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    private function availableYears(int $var): array
    {
        $response = $this->request('list/model/th/domain/' . $this->domain . '/var/' . $var . '/lang/ind');

        return $this->normalizeList($response['data'][1] ?? []);
    }

    private function availablePeriods(int $var): array
    {
        $response = $this->request('list/model/turth/domain/' . $this->domain . '/var/' . $var . '/lang/ind');

        return $this->normalizeList($response['data'][1] ?? []);
    }

    private function fetchData(array $params): array
    {
        $path = 'list/model/data/domain/' . $this->domain . '/lang/ind'
            . '/var/' . $params['var']
            . (isset($params['vervar']) ? '/vervar/' . $params['vervar'] : '')
            . (isset($params['turvar']) ? '/turvar/' . $params['turvar'] : '')
            . '/th/' . $params['th']
            . '/turth/' . $params['turth'];

        return $this->request($path);
    }

    private function request(string $path): array
    {
        $url = $this->baseUrl . '/' . trim($path, '/') . '/key/' . $this->apiKey;
        $response = Http::timeout($this->timeout)->acceptJson()->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('BPS API HTTP ' . $response->status());
        }

        $json = $response->json();

        if (($json['status'] ?? null) === 'Error') {
            throw new \RuntimeException($json['message'] ?? 'BPS API error');
        }

        return $json;
    }

    private function normalizeList(mixed $items): array
    {
        if ($items === null || $items === '') {
            return [];
        }

        if (is_array($items) && array_is_list($items)) {
            return $items;
        }

        return is_array($items) ? [$items] : [];
    }

    private function firstNumericValue(array|object $content): ?float
    {
        foreach ((array) $content as $value) {
            if (is_numeric($value) && (float) $value > 0) {
                return (float) $value;
            }
        }

        return null;
    }

    private function periodDate(int $year, int $month): string
    {
        $month = max(1, min(12, $month ?: 12));

        return Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
    }

    private function storePrice(array $record): int
    {
        CommodityPrice::updateOrCreate(
            [
                'commodity_code' => $record['commodity_code'],
                'region_code' => $record['region_code'],
                'price_date' => $record['price_date'],
            ],
            $record
        );

        return 1;
    }
}
