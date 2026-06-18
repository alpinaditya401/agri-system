<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FertilizerService;
use Illuminate\Support\Facades\DB;
use Exception;

class FertilizerOrderController extends Controller
{
    protected $fertilizerService;

    public function __construct(FertilizerService $fertilizerService)
    {
        $this->fertilizerService = $fertilizerService;
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'distributor_id' => 'required|exists:users,id',
            'fertilizer_type_id' => 'required|exists:fertilizer_types,id',
            'amount_kg' => 'required|integer|min:1',
            'price_per_kg' => 'required|numeric'
        ]);

        $farmer = auth()->user();

        // Normally we check if role is farmer, but we don't have a direct relation if auth()->user() is basic.
        // Assuming user ID maps to farmer. We can check the DB for safety.
        $farmerProfile = DB::table('farmer_profiles')->where('user_id', $farmer->id)->first();
        if (!$farmerProfile) {
            return response()->json(['error' => 'Only registered farmers can purchase subsidized fertilizer.'], 403);
        }

        DB::beginTransaction();
        try {
            // 1. Check and deduct quota via Service
            $this->fertilizerService->validateAndDeductQuota(
                $farmer->id, 
                $request->fertilizer_type_id, 
                $request->amount_kg
            );

            // Fetch the quota ID to tie to the transaction
            $quota = DB::table('fertilizer_quotas')
                ->where('farmer_id', $farmer->id)
                ->where('fertilizer_type_id', $request->fertilizer_type_id)
                ->where('year', date('Y'))
                ->first();

            // 2. Create the Order / Transaction
            $transactionNumber = 'TRX-' . time() . '-' . rand(1000, 9999);
            
            DB::table('fertilizer_transactions')->insert([
                'transaction_number' => $transactionNumber,
                'farmer_id' => $farmer->id,
                'distributor_id' => $request->distributor_id,
                'fertilizer_type_id' => $request->fertilizer_type_id,
                'fertilizer_quota_id' => $quota->id,
                'requested_kg' => $request->amount_kg,
                'price_per_kg' => $request->price_per_kg,
                'total_amount' => $request->amount_kg * $request->price_per_kg,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Fertilizer order created successfully.', 
                'transaction_number' => $transactionNumber
            ], 201);
            
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
