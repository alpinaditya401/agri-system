<?php

// =============================================================================
// FOR LARAVEL 10 — app/Console/Kernel.php
// =============================================================================

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * Schedule overview:
     * ┌─────────────────────────────────────────────────────────────────────┐
     * │  bps:fetch-prices       Daily at 00:05 (midnight + 5 min buffer)   │
     * │  orders:check-expired   Every hour                                  │
     * │  fertilizer:sync-quotas Weekly on Monday 01:00                      │
     * └─────────────────────────────────────────────────────────────────────┘
     */
    protected function schedule(Schedule $schedule): void
    {
        // ── BPS Commodity Price Sync ──────────────────────────────────────────
        // Runs at 00:05 daily — slight offset avoids exact-midnight spike.
        // Emails output to admin on failure.
        $schedule->command('bps:fetch-prices')
                 ->dailyAt('00:05')
                 ->withoutOverlapping(30)         // prevent stacking if slow
                 ->onOneServer()                  // for multi-server deployments
                 ->runInBackground()
                 ->onFailure(function () {
                     \Illuminate\Support\Facades\Log::error('Scheduled BPS fetch failed.');
                 })
                 ->emailOutputOnFailure(config('mail.admin_address'));

        // ── Auto-cancel unpaid orders after 24h ──────────────────────────────
        $schedule->command('orders:cancel-expired')
                 ->hourly()
                 ->withoutOverlapping();

        // ── Weekly fertilizer quota reminder to farmers ───────────────────────
        $schedule->command('fertilizer:quota-reminder')
                 ->weeklyOn(1, '08:00');          // Monday at 08:00

        // ── Prune stale BPS fetch logs (keep 90 days) ─────────────────────────
        $schedule->command('bps:prune-logs --days=90')
                 ->monthly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}


// =============================================================================
// FOR LARAVEL 11 — routes/console.php  (replaces Kernel.php scheduling)
// Paste this block into your routes/console.php file
// =============================================================================

/*
use Illuminate\Support\Facades\Schedule;

Schedule::command('bps:fetch-prices')
    ->dailyAt('00:05')
    ->withoutOverlapping(30)
    ->onOneServer()
    ->runInBackground()
    ->onFailure(fn () => \Log::error('Scheduled BPS fetch failed.'))
    ->emailOutputOnFailure(config('mail.admin_address'));

Schedule::command('orders:cancel-expired')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('fertilizer:quota-reminder')
    ->weeklyOn(1, '08:00');

Schedule::command('bps:prune-logs --days=90')
    ->monthly();
*/


// =============================================================================
// CRON ENTRY — Add to server crontab (crontab -e):
// * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
// =============================================================================
