<?php

namespace App\Console\Commands;

use App\Models\BpsFetchLog;
use App\Models\CommodityPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchBpsDataCommand extends Command
{
    protected $signature = 'bps:fetch-prices {--simulate : Use simulated data instead of real API}';
    protected $description = 'Fetch commodity prices from BPS API and store them locally';

    public function handle()
    {
        $this->info('Starting BPS data fetch...');
        $startTime = microtime(true);

        $apiKey = config('services.bps.api_key');
        $endpoint = 'https://webapi.bps.go.id/v1/api/list/model/bulletin/domain/0000/key/' . $apiKey;

        $recordsInserted = 0;

        try {
            if ($this->option('simulate') || empty($apiKey)) {
                $this->warn('Using simulated data (no valid BPS API key configured).');
                $recordsInserted = $this->storeSimulatedData();
            } else {
                $recordsInserted = $this->fetchFromApi($endpoint);
            }

            BpsFetchLog::create([
                'endpoint'         => $endpoint,
                'status'           => 'success',
                'records_fetched'  => $recordsInserted,
                'response_time_ms' => round((microtime(true) - $startTime) * 1000),
                'fetched_at'       => now(),
            ]);

            $this->info("Successfully fetched and stored {$recordsInserted} prices.");

            return self::SUCCESS;
        } catch (\Exception $e) {
            Log::error('BPS API Fetch Error: ' . $e->getMessage());

            BpsFetchLog::create([
                'endpoint'         => $endpoint,
                'status'           => 'failed',
                'records_fetched'  => 0,
                'error_message'    => $e->getMessage(),
                'response_time_ms' => round((microtime(true) - $startTime) * 1000),
                'fetched_at'       => now(),
            ]);

            $this->error('Failed to fetch BPS data: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function fetchFromApi(string $endpoint): int
    {
        $response = Http::timeout(60)->get($endpoint);

        if (!$response->successful()) {
            throw new \RuntimeException('BPS API returned status ' . $response->status());
        }

        $data = $response->json();
        $recordsInserted = 0;

        // Adjust parsing logic based on actual BPS API response structure
        $items = $data['data'][1] ?? [];

        foreach ($items as $item) {
            CommodityPrice::updateOrCreate(
                [
                    'commodity_name' => $item['title'] ?? 'Unknown',
                    'price_date'     => Carbon::today(),
                    'region'         => $item['region'] ?? 'Nasional',
                ],
                [
                    'commodity_code' => $item['id'] ?? null,
                    'category'       => $item['category'] ?? 'Lainnya',
                    'price'          => $item['price'] ?? 0,
                    'unit'           => $item['unit'] ?? 'kg',
                    'region_code'    => $item['region_code'] ?? null,
                    'source'         => 'BPS',
                    'raw_data'       => $item,
                ]
            );
            $recordsInserted++;
        }

        return $recordsInserted;
    }

    private function storeSimulatedData(): int
    {
        $simulatedData = [
            ['commodity_name' => 'Beras Medium', 'category' => 'Pangan', 'price' => 14500, 'unit' => 'kg'],
            ['commodity_name' => 'Jagung Pipilan Kering', 'category' => 'Pangan', 'price' => 7500, 'unit' => 'kg'],
            ['commodity_name' => 'Cabai Rawit Merah', 'category' => 'Hortikultura', 'price' => 45000, 'unit' => 'kg'],
            ['commodity_name' => 'Bawang Merah', 'category' => 'Hortikultura', 'price' => 32000, 'unit' => 'kg'],
            ['commodity_name' => 'Kentang', 'category' => 'Hortikultura', 'price' => 22000, 'unit' => 'kg'],
            ['commodity_name' => 'Tomat', 'category' => 'Hortikultura', 'price' => 18000, 'unit' => 'kg'],
            ['commodity_name' => 'Kedelai', 'category' => 'Pangan', 'price' => 12000, 'unit' => 'kg'],
            ['commodity_name' => 'Gula Pasir', 'category' => 'Pangan', 'price' => 16500, 'unit' => 'kg'],
        ];

        foreach ($simulatedData as $item) {
            CommodityPrice::create([
                'commodity_name' => $item['commodity_name'],
                'category'       => $item['category'],
                'price'          => $item['price'],
                'unit'           => $item['unit'],
                'source'         => 'BPS',
                'price_date'     => Carbon::today(),
            ]);
        }

        return count($simulatedData);
    }
}
