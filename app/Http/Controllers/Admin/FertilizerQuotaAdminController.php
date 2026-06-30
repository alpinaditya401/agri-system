<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FertilizerQuota;
use App\Models\FertilizerType;
use App\Models\User;
use App\Services\FertilizerQuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FertilizerQuotaAdminController extends Controller
{
    public function __construct(private readonly FertilizerQuotaService $quotaService)
    {
    }

    /**
     * Show quota allocation dashboard.
     */
    public function index(): View
    {
        $quotas = FertilizerQuota::with(['farmer', 'fertilizerType', 'allocatedBy'])
            ->where('year', now()->year)
            ->latest()
            ->paginate(25);

        $types = FertilizerType::where('is_active', true)->get();

        return view('admin.fertilizer.quota', compact('quotas', 'types'));
    }

    /**
     * Allocate quota to a single farmer.
     */
    public function allocate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'farmer_id'          => ['required', 'exists:users,id'],
            'fertilizer_type_id' => ['required', 'exists:fertilizer_types,id'],
            'allocated_kg'       => ['required', 'integer', 'min:1', 'max:10000'],
            'season'             => ['required', 'in:MT1,MT2'],
            'year'               => ['required', 'integer', 'min:2020', 'max:2030'],
        ]);

        try {
            $this->quotaService->allocateQuota(
                farmerId: $validated['farmer_id'],
                fertilizerTypeId: $validated['fertilizer_type_id'],
                allocatedKg: $validated['allocated_kg'],
                season: $validated['season'],
                year: $validated['year'],
                allocatedBy: auth()->id(),
            );

            return back()->with('success', 'Kuota berhasil dialokasikan.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['allocate' => $e->getMessage()]);
        }
    }

    /**
     * Bulk allocate quotas to multiple farmers.
     */
    public function bulkAllocate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'farmer_ids'         => ['required', 'array'],
            'farmer_ids.*'       => ['exists:users,id'],
            'fertilizer_type_id' => ['required', 'exists:fertilizer_types,id'],
            'allocated_kg'       => ['required', 'integer', 'min:1', 'max:10000'],
            'season'             => ['required', 'in:MT1,MT2'],
            'year'               => ['required', 'integer', 'min:2020', 'max:2030'],
        ]);

        $successCount = 0;
        $failures = [];

        foreach ($validated['farmer_ids'] as $farmerId) {
            try {
                $this->quotaService->allocateQuota(
                    farmerId: $farmerId,
                    fertilizerTypeId: $validated['fertilizer_type_id'],
                    allocatedKg: $validated['allocated_kg'],
                    season: $validated['season'],
                    year: $validated['year'],
                    allocatedBy: auth()->id(),
                );
                $successCount++;
            } catch (\InvalidArgumentException $e) {
                $failures[] = "Farmer ID {$farmerId}: " . $e->getMessage();
            }
        }

        $message = "Berhasil mengalokasikan {$successCount} kuota.";
        if (!empty($failures)) {
            $message .= ' Gagal: ' . implode(', ', $failures);
        }

        return back()->with('success', $message);
    }

    /**
     * Show quota usage report.
     */
    public function report(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);

        $report = $this->quotaService->getStockMovementReport($year);

        return view('admin.fertilizer.report', compact('report', 'year'));
    }
}
