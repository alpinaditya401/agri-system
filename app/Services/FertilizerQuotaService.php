<?php

namespace App\Services;

use App\Models\FertilizerQuota;
use App\Models\FertilizerStock;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * FertilizerQuotaService
 *
 * Centralizes quota logic so it can be reused across controllers,
 * jobs, and API endpoints without duplication.
 */
class FertilizerQuotaService
{
    /**
     * Get all quotas for a farmer in the current season(s).
     */
    public function getCurrentSeasonQuotas(int $farmerId): Collection
    {
        return FertilizerQuota::with('fertilizerType')
            ->where('farmer_id', $farmerId)
            ->where('year', now()->year)
            ->whereIn('season', $this->getCurrentSeasons())
            ->get();
    }

    /**
     * Get quota for a specific fertilizer type for a farmer.
     */
    public function getQuotaForType(int $farmerId, int $fertilizerTypeId): ?FertilizerQuota
    {
        return FertilizerQuota::where('farmer_id', $farmerId)
            ->where('fertilizer_type_id', $fertilizerTypeId)
            ->where('year', now()->year)
            ->whereIn('season', $this->getCurrentSeasons())
            ->first();
    }

    /**
     * Determine current planting seasons based on month.
     * Indonesian agricultural calendar:
     *   MT1 (Musim Tanam 1): October – March
     *   MT2 (Musim Tanam 2): April – September
     */
    public function getCurrentSeasons(): array
    {
        $month = now()->month;
        if ($month >= 10 || $month <= 3) {
            return ['MT1'];
        }
        return ['MT2'];
    }

    /**
     * Allocate quota for a farmer (called by admin/distributor).
     *
     * @throws \InvalidArgumentException if farmer is not verified
     */
    public function allocateQuota(
        int $farmerId,
        int $fertilizerTypeId,
        int $allocatedKg,
        string $season,
        int $year,
        int $allocatedBy,
    ): FertilizerQuota {
        $farmer = User::with('farmerProfile')->findOrFail($farmerId);

        if ($farmer->farmerProfile?->verification_status !== 'verified') {
            throw new \InvalidArgumentException(
                "Petani dengan ID {$farmerId} belum terverifikasi. Kuota tidak dapat dialokasikan."
            );
        }

        return FertilizerQuota::updateOrCreate(
            [
                'farmer_id'          => $farmerId,
                'fertilizer_type_id' => $fertilizerTypeId,
                'year'               => $year,
                'season'             => $season,
            ],
            [
                'allocated_kg'    => $allocatedKg,
                'used_kg'         => 0,
                'allocated_by'    => $allocatedBy,
                'quota_expires_at'=> now()->addMonths(6),
            ]
        );
    }

    /**
     * Mark quota as used when a fertilizer transaction is dispensed.
     * Called by DistributorFertilizerController when dispensing.
     */
    public function markAsUsed(int $quotaId, int $usedKg): FertilizerQuota
    {
        $quota = FertilizerQuota::lockForUpdate()->findOrFail($quotaId);

        if ($usedKg > $quota->remaining_kg) {
            throw new \RuntimeException(
                "Jumlah penggunaan ({$usedKg} kg) melebihi sisa kuota ({$quota->remaining_kg} kg)."
            );
        }

        $quota->increment('used_kg', $usedKg);
        $quota->refresh();

        return $quota;
    }

    /**
     * Get nearby distributors who have stock of a given fertilizer type.
     * Ordered by distance from farmer's coordinates (Haversine formula in SQL).
     */
    public function getNearbyDistributors(
        ?float $farmerLat,
        ?float $farmerLng,
        int $fertilizerTypeId,
        float $radiusKm = 50
    ): Collection {
        if (!$farmerLat || !$farmerLng) {
            // Fall back to all distributors with stock if no coords
            return FertilizerStock::with('distributor')
                ->where('fertilizer_type_id', $fertilizerTypeId)
                ->whereRaw('(stock_kg - reserved_kg) > 0')
                ->get()
                ->pluck('distributor');
        }

        // Haversine formula to filter by distance
        $haversine = "(6371 * ACOS(
            COS(RADIANS({$farmerLat})) * COS(RADIANS(users.latitude)) *
            COS(RADIANS(users.longitude) - RADIANS({$farmerLng})) +
            SIN(RADIANS({$farmerLat})) * SIN(RADIANS(users.latitude))
        ))";

        return User::select('users.*')
            ->selectRaw("{$haversine} AS distance_km")
            ->join('fertilizer_stocks', 'fertilizer_stocks.distributor_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->where('roles.name', 'distributor')
            ->where('fertilizer_stocks.fertilizer_type_id', $fertilizerTypeId)
            ->whereRaw('(fertilizer_stocks.stock_kg - fertilizer_stocks.reserved_kg) > 0')
            ->whereRaw("{$haversine} <= ?", [$radiusKm])
            ->orderBy('distance_km')
            ->get();
    }

    /**
     * Generate a unique transaction number.
     * Format: PUPUK-YYYYMMDD-XXXX (e.g. PUPUK-20240115-0042)
     */
    public function generateTransactionNumber(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "PUPUK-{$date}-";

        $last = \App\Models\FertilizerTransaction::where('transaction_number', 'like', "{$prefix}%")
            ->orderByDesc('transaction_number')
            ->value('transaction_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Admin summary: stock movements report.
     * Shows total distributed per distributor per fertilizer type per month.
     */
    public function getStockMovementReport(int $year, ?int $distributorId = null): Collection
    {
        $monthExpression = match (config('database.default')) {
            'sqlite' => "CAST(strftime('%m', fertilizer_transactions.dispensed_at) AS INTEGER)",
            'pgsql' => 'EXTRACT(MONTH FROM fertilizer_transactions.dispensed_at)',
            default => 'MONTH(fertilizer_transactions.dispensed_at)',
        };

        return \App\Models\FertilizerTransaction::query()
            ->selectRaw("
                fertilizer_transactions.distributor_id,
                users.name AS distributor_name,
                fertilizer_types.name AS fertilizer_name,
                {$monthExpression} AS month,
                COUNT(*) AS transaction_count,
                SUM(fertilizer_transactions.approved_kg) AS total_kg_dispensed,
                SUM(fertilizer_transactions.total_amount) AS total_value
            ")
            ->join('users', 'users.id', '=', 'fertilizer_transactions.distributor_id')
            ->join('fertilizer_types', 'fertilizer_types.id', '=', 'fertilizer_transactions.fertilizer_type_id')
            ->where('fertilizer_transactions.status', 'dispensed')
            ->whereYear('fertilizer_transactions.dispensed_at', $year)
            ->when($distributorId, fn($q) => $q->where('fertilizer_transactions.distributor_id', $distributorId))
            ->groupBy(
                'fertilizer_transactions.distributor_id',
                'users.name',
                'fertilizer_types.name',
                \DB::raw($monthExpression)
            )
            ->orderBy('users.name')
            ->orderBy('month')
            ->get();
    }
}
