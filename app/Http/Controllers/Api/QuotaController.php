<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FertilizerQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class QuotaController extends Controller
{
    public function __construct(private readonly FertilizerQuotaService $quotaService)
    {
    }

    public function mine(): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isFarmer()) {
            return response()->json(['error' => 'Only farmers have quotas.'], 403);
        }

        $quotas = $this->quotaService->getCurrentSeasonQuotas($user->id);

        return response()->json([
            'farmer_id' => $user->id,
            'season'    => $this->quotaService->getCurrentSeasons(),
            'quotas'    => $quotas->map(fn($q) => [
                'fertilizer_type' => $q->fertilizerType?->name,
                'allocated_kg'    => $q->allocated_kg,
                'used_kg'         => $q->used_kg,
                'remaining_kg'    => $q->remaining_kg,
                'year'            => $q->year,
                'season'          => $q->season,
                'expires_at'      => $q->quota_expires_at,
            ]),
        ]);
    }
}
