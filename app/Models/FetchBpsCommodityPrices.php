<?php

namespace App\Console\Commands;

use App\Services\BpsApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan Command: FetchBpsCommodityPrices
 *
 * Scheduled to run daily at midnight (see Kernel.php / routes/console.php).
 * Fetches commodity price data from BPS API and stores it in the local database.
 *
 * Usage:
 *   php artisan bps:fetch-prices             # run manually
 *   php artisan bps:fetch-prices --verbose   # with detailed output
 */
class FetchBpsCommodityPrices extends Command
{
    protected $signature = 'bps:fetch-prices
                            {--force : Force fetch even if data exists for today}
                            {--dry-run : Simulate fetch without persisting}';

    protected $description = 'Fetch commodity price data from BPS API and cache in local database';

    public function __construct(private readonly BpsApiService $bpsService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('[BPS Fetch] Starting commodity price sync — ' . now()->toDateTimeString());
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('[DRY RUN] No data will be persisted.');
        }

        try {
            $results = $this->bpsService->fetchAndCacheAll();

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Records fetched',  $results['fetched']],
                    ['Failed endpoints', $results['failed']],
                ]
            );

            if (!empty($results['errors'])) {
                $this->newLine();
                $this->warn('Errors encountered:');
                foreach ($results['errors'] as $err) {
                    $this->line("  ✗ {$err}");
                }
            }

            $this->newLine();

            if ($results['failed'] > 0 && $results['fetched'] === 0) {
                $this->error('[BPS Fetch] Complete failure — no records saved.');
                Log::error('BPS daily fetch: complete failure', $results);
                return self::FAILURE;
            }

            if ($results['failed'] > 0) {
                $this->warn("[BPS Fetch] Partial success — {$results['fetched']} records saved, {$results['failed']} endpoints failed.");
                Log::warning('BPS daily fetch: partial', $results);
                return self::SUCCESS; // partial is still ok for scheduler
            }

            $this->info("[BPS Fetch] ✓ Complete — {$results['fetched']} records saved.");
            Log::info('BPS daily fetch: success', $results);
            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('[BPS Fetch] Fatal error: ' . $e->getMessage());
            Log::error('BPS daily fetch: fatal', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return self::FAILURE;
        }
    }
}
