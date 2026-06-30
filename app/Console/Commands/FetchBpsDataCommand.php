<?php

namespace App\Console\Commands;

use App\Models\BpsFetchLog;
use App\Services\BpsApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FetchBpsDataCommand extends Command
{
    protected $signature = 'bps:fetch-prices
                            {--dry-run : Test BPS fetch without persisting changes}
                            {--force : Reserved for manual refresh compatibility}';

    protected $description = 'Fetch national rupiah commodity reference prices from BPS WebAPI';

    public function __construct(private readonly BpsApiService $bpsService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('[BPS] Sinkronisasi harga komoditas nasional dimulai...');
        $startTime = microtime(true);

        try {
            if ($this->option('dry-run')) {
                DB::beginTransaction();
            }

            $results = $this->bpsService->fetchAndCacheAll();

            if ($this->option('dry-run')) {
                DB::rollBack();
            }

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Records saved', $results['fetched']],
                    ['Unavailable/failed series', $results['failed']],
                ]
            );

            foreach ($results['errors'] as $error) {
                $this->warn($error);
            }

            BpsFetchLog::create([
                'endpoint' => config('services.bps.base_url') . ' domain=' . config('services.bps.domain', '0000'),
                'status' => $results['failed'] > 0 ? 'partial' : 'success',
                'records_fetched' => $results['fetched'],
                'error_message' => $results['errors'] ? implode("\n", $results['errors']) : null,
                'response_time_ms' => round((microtime(true) - $startTime) * 1000),
                'fetched_at' => now(),
            ]);

            $this->info('[BPS] Selesai.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            if ($this->option('dry-run') && DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error('BPS API Fetch Error: ' . $e->getMessage());

            BpsFetchLog::create([
                'endpoint' => config('services.bps.base_url') . ' domain=' . config('services.bps.domain', '0000'),
                'status' => 'failed',
                'records_fetched' => 0,
                'error_message' => $e->getMessage(),
                'response_time_ms' => round((microtime(true) - $startTime) * 1000),
                'fetched_at' => now(),
            ]);

            $this->error('[BPS] Gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
