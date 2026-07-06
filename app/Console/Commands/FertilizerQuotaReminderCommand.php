<?php

namespace App\Console\Commands;

use App\Models\FertilizerQuota;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FertilizerQuotaReminderCommand extends Command
{
    protected $signature = 'fertilizer:quota-reminder';
    protected $description = 'Send daily fertilizer quota reminders to farmers with low remaining quota';

    public function handle()
    {
        $quotas = FertilizerQuota::with('farmer', 'fertilizerType')
            ->where('year', now()->year)
            ->whereIn('season', $this->getCurrentSeasons())
            ->whereRaw('(allocated_kg - used_kg) > 0')
            ->get()
            ->filter(fn (FertilizerQuota $quota) => $this->isQuotaLow($quota));

        $farmerIds = $quotas->pluck('farmer_id')->unique();
        $sentCount = 0;

        foreach ($farmerIds as $farmerId) {
            $farmerQuotas = $quotas->where('farmer_id', $farmerId);
            $farmer = User::find($farmerId);

            if (!$farmer) {
                continue;
            }

            $remaining = $farmerQuotas->sum(fn ($q) => $q->remaining_kg);
            $fertilizers = $farmerQuotas
                ->map(fn ($quota) => $quota->fertilizerType?->name)
                ->filter()
                ->unique()
                ->join(', ');

            $title = 'Kuota pupuk menipis';
            $message = "Sisa kuota pupuk Anda tinggal {$remaining} kg untuk {$fertilizers}. Ajukan atau pantau penggunaan sebelum kuota habis.";
            $link = route('farmer.fertilizer.index');

            if ($this->alreadySentToday($farmer->id, $title)) {
                $this->line("Skipped: reminder for {$farmer->name} already sent today.");
                continue;
            }

            Notification::sendToUser(
                userId: $farmer->id,
                tipe: 'low_stock',
                judul: $title,
                pesan: $message,
                link: $link,
            );

            $sentCount++;

            $this->info("Reminder: Farmer {$farmer->name} has {$remaining} kg low remaining quota.");
            Log::info('Quota reminder notification sent to farmer', [
                'farmer_id' => $farmerId,
                'remaining_kg' => $remaining,
            ]);
        }

        $this->info("Processed quota reminders for {$farmerIds->count()} farmers. Sent {$sentCount} notifications.");

        return self::SUCCESS;
    }

    private function isQuotaLow(FertilizerQuota $quota): bool
    {
        if ((int) $quota->allocated_kg <= 0) {
            return false;
        }

        $remaining = (int) $quota->remaining_kg;
        $thresholdByPercent = (int) ceil($quota->allocated_kg * 0.25);
        $threshold = max(50, $thresholdByPercent);

        return $remaining <= $threshold;
    }

    private function alreadySentToday(int $farmerId, string $title): bool
    {
        return Notification::query()
            ->where('user_id', $farmerId)
            ->where('tipe', 'low_stock')
            ->where('judul', $title)
            ->whereDate('created_at', today())
            ->exists();
    }

    private function getCurrentSeasons(): array
    {
        $month = now()->month;
        return ($month >= 10 || $month <= 3) ? ['MT1'] : ['MT2'];
    }
}
