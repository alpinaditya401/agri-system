<?php

namespace App\Console\Commands;

use App\Models\FertilizerQuota;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FertilizerQuotaReminderCommand extends Command
{
    protected $signature = 'fertilizer:quota-reminder';
    protected $description = 'Send weekly fertilizer quota reminders to farmers with remaining quota';

    public function handle()
    {
        $quotas = FertilizerQuota::with('farmer', 'fertilizerType')
            ->where('year', now()->year)
            ->whereIn('season', $this->getCurrentSeasons())
            ->whereRaw('(allocated_kg - used_kg) > 0')
            ->get();

        $farmerIds = $quotas->pluck('farmer_id')->unique();

        foreach ($farmerIds as $farmerId) {
            $farmerQuotas = $quotas->where('farmer_id', $farmerId);
            $farmer = User::find($farmerId);

            if (!$farmer) {
                continue;
            }

            $remaining = $farmerQuotas->sum(fn ($q) => $q->remaining_kg);

            $this->info("Reminder: Farmer {$farmer->name} has {$remaining} kg remaining quota.");
            Log::info("Quota reminder sent to farmer", [
                'farmer_id' => $farmerId,
                'remaining_kg' => $remaining,
            ]);

            // TODO: Integrate with notification system (email/SMS/WhatsApp)
        }

        $this->info("Processed quota reminders for {$farmerIds->count()} farmers.");

        return self::SUCCESS;
    }

    private function getCurrentSeasons(): array
    {
        $month = now()->month;
        return ($month >= 10 || $month <= 3) ? ['MT1'] : ['MT2'];
    }
}
