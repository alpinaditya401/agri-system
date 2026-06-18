<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

class FertilizerService
{
    /**
     * Validate and deduct subsidy quota for a farmer.
     * 
     * @param int $farmerId
     * @param int $fertilizerTypeId
     * @param int $requestedAmountKg
     * @return bool
     * @throws Exception
     */
    public function validateAndDeductQuota($farmerId, $fertilizerTypeId, $requestedAmountKg)
    {
        // Find active quota for current year
        $quota = DB::table('fertilizer_quotas')
            ->where('farmer_id', $farmerId)
            ->where('fertilizer_type_id', $fertilizerTypeId)
            ->where('year', date('Y'))
            ->lockForUpdate()
            ->first();

        if (!$quota) {
            throw new Exception("No subsidy quota found for this fertilizer type in the current year.");
        }

        if ($quota->remaining_kg < $requestedAmountKg) {
            throw new Exception("Insufficient subsidy quota. Remaining quota: {$quota->remaining_kg} Kg.");
        }

        // Deduct quota
        DB::table('fertilizer_quotas')
            ->where('id', $quota->id)
            ->update([
                'used_kg' => DB::raw("used_kg + $requestedAmountKg"),
                'updated_at' => now()
            ]);

        return true;
    }
}
