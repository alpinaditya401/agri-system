<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\FertilizerStock;
use App\Models\FertilizerTransaction;
use App\Services\FertilizerQuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FertilizerTransactionController extends Controller
{
    public function __construct(private readonly FertilizerQuotaService $quotaService)
    {
    }

    public function index(): View
    {
        $transactions = FertilizerTransaction::with(['farmer', 'fertilizerType'])
            ->where('distributor_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('distributor.transactions.index', compact('transactions'));
    }

    public function show(FertilizerTransaction $transaction): View
    {
        $this->authorizeDistributorTransaction($transaction);

        $transaction->load(['farmer.farmerProfile', 'fertilizerType', 'quota']);

        return view('distributor.transactions.show', compact('transaction'));
    }

    public function approve(Request $request, FertilizerTransaction $transaction): RedirectResponse
    {
        $this->authorizeDistributorTransaction($transaction);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi sudah tidak dapat disetujui.');
        }

        $validated = $request->validate([
            'approved_kg' => ['required', 'integer', 'min:1', "max:{$transaction->requested_kg}"],
        ]);

        $approvedKg = $validated['approved_kg'];

        // Check distributor stock
        $stock = FertilizerStock::where('distributor_id', Auth::id())
            ->where('fertilizer_type_id', $transaction->fertilizer_type_id)
            ->first();

        $available = ($stock?->stock_kg ?? 0) - ($stock?->reserved_kg ?? 0);

        if ($available < $approvedKg) {
            return back()->withErrors(['stock' => "Stok tidak mencukupi. Tersedia: {$available} kg."]);
        }

        $transaction->update([
            'status'       => 'approved',
            'approved_kg'  => $approvedKg,
            'total_amount' => $approvedKg * $transaction->price_per_kg,
            'approved_at'  => now(),
            'processed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Permintaan pupuk berhasil disetujui.');
    }

    public function reject(Request $request, FertilizerTransaction $transaction): RedirectResponse
    {
        $this->authorizeDistributorTransaction($transaction);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi sudah tidak dapat ditolak.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        // Release reserved stock
        $stock = FertilizerStock::where('distributor_id', Auth::id())
            ->where('fertilizer_type_id', $transaction->fertilizer_type_id)
            ->first();

        if ($stock) {
            $stock->decrement('reserved_kg', $transaction->requested_kg);
        }

        $transaction->update([
            'status'           => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'processed_by'     => Auth::id(),
        ]);

        return back()->with('success', 'Permintaan pupuk ditolak.');
    }

    public function dispense(FertilizerTransaction $transaction): RedirectResponse
    {
        $this->authorizeDistributorTransaction($transaction);

        if ($transaction->status !== 'approved') {
            return back()->with('error', 'Transaksi harus disetujui terlebih dahulu sebelum diserahkan.');
        }

        $approvedKg = $transaction->approved_kg ?? $transaction->requested_kg;

        // Deduct stock and release reservation
        $stock = FertilizerStock::where('distributor_id', Auth::id())
            ->where('fertilizer_type_id', $transaction->fertilizer_type_id)
            ->first();

        if ($stock) {
            $stock->decrement('stock_kg', $approvedKg);
            $stock->decrement('reserved_kg', $transaction->requested_kg);
        }

        // Mark quota as used
        $this->quotaService->markAsUsed($transaction->fertilizer_quota_id, $approvedKg);

        $transaction->update([
            'status'       => 'dispensed',
            'dispensed_at' => now(),
            'processed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Pupuk berhasil diserahkan ke petani.');
    }

    private function authorizeDistributorTransaction(FertilizerTransaction $transaction): void
    {
        if ($transaction->distributor_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Transaksi ini bukan milik Anda.');
        }
    }
}
