<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\FertilizerTransaction;
use App\Models\FertilizerQuota;
use App\Models\FertilizerType;
use App\Models\FertilizerStock;
use App\Models\Notification;
use App\Services\FertilizerQuotaService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Farmer Fertilizer Controller
 *
 * Handles fertilizer purchase requests from verified farmers.
 * Core security: quota is checked server-side — never trust client input.
 */
class FertilizerController extends Controller
{
    public function __construct(private readonly FertilizerQuotaService $quotaService)
    {
    }

    /**
     * Show available fertilizer types and farmer's remaining quota.
     */
    public function index(): View
    {
        $farmer = Auth::user();
        $types  = FertilizerType::where('is_active', true)->get();

        // Attach quota info to each type for this farmer in the current season
        $quotas = $this->quotaService->getCurrentSeasonQuotas($farmer->id);
        $types->each(function ($type) use ($quotas) {
            $quota = $quotas->firstWhere('fertilizer_type_id', $type->id);
            $type->farmer_quota        = $quota;
            $type->remaining_kg        = $quota?->remaining_kg ?? 0;
            $type->allocated_kg        = $quota?->allocated_kg ?? 0;
        });

        return view('farmer.fertilizer.index', compact('types', 'quotas'));
    }

    /**
     * Show purchase request form.
     */
    public function create(FertilizerType $type): View
    {
        $farmer = Auth::user();
        $quota  = $this->quotaService->getQuotaForType($farmer->id, $type->id);

        // Show available distributors near the farmer
        $distributors = $this->quotaService->getNearbyDistributors(
            $farmer->latitude,
            $farmer->longitude,
            $type->id,
            radiusKm: 50
        );

        return view('farmer.fertilizer.create', compact('type', 'quota', 'distributors'));
    }

    /**
     * Submit a fertilizer purchase request.
     *
     * ═══════════════════════════════════════════════════════════════
     * CORE QUOTA VALIDATION LOGIC
     * ═══════════════════════════════════════════════════════════════
     * Server enforces:
     *  1. Farmer must be verified
     *  2. Quota must exist for current year/season
     *  3. remaining_kg >= requested_kg (strictly)
     *  4. Distributor must have sufficient stock
     *  5. All within a DB transaction with row-level locking
     * ═══════════════════════════════════════════════════════════════
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fertilizer_type_id' => ['required', 'exists:fertilizer_types,id'],
            'distributor_id'     => ['required', 'exists:users,id'],
            'requested_kg'       => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $farmer    = Auth::user();
        $requestedKg = (int) $validated['requested_kg'];

        DB::beginTransaction();
        try {
            // ── Step 1: Lock the quota row to prevent race conditions ─────────
            $quota = FertilizerQuota::where('farmer_id', $farmer->id)
                ->where('fertilizer_type_id', $validated['fertilizer_type_id'])
                ->where('year', now()->year)
                ->whereIn('season', $this->quotaService->getCurrentSeasons())
                ->lockForUpdate()   // SELECT ... FOR UPDATE
                ->first();

            // ── Step 2: Quota existence check ─────────────────────────────────
            if (!$quota) {
                DB::rollBack();
                return back()->withErrors([
                    'quota' => 'Anda tidak memiliki kuota pupuk bersubsidi untuk musim tanam ini. '
                             . 'Hubungi kelompok tani Anda untuk pengalokasian kuota.',
                ])->withInput();
            }

            // ── Step 3: Quota expiry check ────────────────────────────────────
            if ($quota->quota_expires_at && now()->isAfter($quota->quota_expires_at)) {
                DB::rollBack();
                return back()->withErrors([
                    'quota' => 'Kuota pupuk Anda telah kadaluarsa pada '
                             . $quota->quota_expires_at->format('d M Y') . '.',
                ])->withInput();
            }

            // ── Step 4: Remaining quota check ─────────────────────────────────
            if ($quota->remaining_kg <= 0) {
                DB::rollBack();
                return back()->withErrors([
                    'quota' => 'Kuota pupuk bersubsidi Anda sudah habis. '
                             . "Alokasi: {$quota->allocated_kg} kg | Terpakai: {$quota->used_kg} kg.",
                ])->withInput();
            }

            if ($requestedKg > $quota->remaining_kg) {
                DB::rollBack();
                return back()->withErrors([
                    'requested_kg' => "Jumlah permintaan ({$requestedKg} kg) melebihi sisa kuota "
                                   . "({$quota->remaining_kg} kg). Maksimal yang dapat Anda ajukan: "
                                   . "{$quota->remaining_kg} kg.",
                ])->withInput();
            }

            // ── Step 5: Distributor stock check ───────────────────────────────
            $stock = FertilizerStock::where('distributor_id', $validated['distributor_id'])
                ->where('fertilizer_type_id', $validated['fertilizer_type_id'])
                ->lockForUpdate()
                ->first();

            $availableStock = ($stock->stock_kg ?? 0) - ($stock->reserved_kg ?? 0);

            if (!$stock || $availableStock < $requestedKg) {
                DB::rollBack();
                return back()->withErrors([
                    'distributor_id' => "Stok distributor tidak mencukupi. "
                                      . "Tersedia: {$availableStock} kg. Pilih distributor lain atau kurangi jumlah.",
                ])->withInput();
            }

            // ── Step 6: Check for pending transactions (prevent duplicates) ───
            $pendingCount = FertilizerTransaction::where('farmer_id', $farmer->id)
                ->where('fertilizer_type_id', $validated['fertilizer_type_id'])
                ->whereIn('status', ['pending', 'approved'])
                ->count();

            if ($pendingCount > 0) {
                DB::rollBack();
                return back()->withErrors([
                    'duplicate' => 'Anda masih memiliki permintaan pupuk yang sedang diproses. '
                                 . 'Selesaikan atau batalkan permintaan sebelumnya terlebih dahulu.',
                ])->withInput();
            }

            // ── Step 7: All checks passed — create the transaction ────────────
            $fertilizerType = FertilizerType::findOrFail($validated['fertilizer_type_id']);

            $transaction = FertilizerTransaction::create([
                'transaction_number' => $this->quotaService->generateTransactionNumber(),
                'farmer_id'          => $farmer->id,
                'distributor_id'     => $validated['distributor_id'],
                'fertilizer_type_id' => $validated['fertilizer_type_id'],
                'fertilizer_quota_id'=> $quota->id,
                'requested_kg'       => $requestedKg,
                'price_per_kg'       => $fertilizerType->subsidy_price_per_kg,
                'status'             => 'pending',
            ]);

            // Reserve stock (soft-hold) until distributor approves/rejects
            $stock->increment('reserved_kg', $requestedKg);

            DB::commit();

            Notification::sendToUser(
                userId: (int) $validated['distributor_id'],
                tipe: 'info',
                judul: 'Permintaan pupuk baru',
                pesan: "Petani {$farmer->name} mengajukan {$requestedKg} kg {$fertilizerType->name}.",
                link: route('distributor.fertilizer.show', $transaction),
            );

            return redirect()->route('farmer.fertilizer.transactions.show', $transaction)
                ->with('success', "Permintaan pupuk berhasil diajukan! "
                               . "No. Transaksi: {$transaction->transaction_number}. "
                               . "Menunggu konfirmasi distributor.");

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Fertilizer transaction failed', [
                'farmer_id' => $farmer->id,
                'error'     => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem. Coba lagi.'])->withInput();
        }
    }

    /**
     * Show the farmer's fertilizer transaction history.
     */
    public function history(): View
    {
        $transactions = FertilizerTransaction::with(['fertilizerType', 'distributor'])
            ->where('farmer_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('farmer.fertilizer.history', compact('transactions'));
    }

    /**
     * Show a single transaction's detail.
     */
    public function showTransaction(FertilizerTransaction $transaction): View
    {
        $this->authorizeFarmerTransaction($transaction);

        $transaction->load(['fertilizerType', 'distributor', 'farmer', 'quota']);

        return view('farmer.fertilizer.transaction_show', compact('transaction'));
    }

    /**
     * Cancel a pending fertilizer transaction and release reserved stock.
     */
    public function cancel(FertilizerTransaction $transaction): RedirectResponse
    {
        $this->authorizeFarmerTransaction($transaction);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Hanya permintaan yang masih pending yang dapat dibatalkan.');
        }

        DB::beginTransaction();
        try {
            $stock = FertilizerStock::where('distributor_id', $transaction->distributor_id)
                ->where('fertilizer_type_id', $transaction->fertilizer_type_id)
                ->lockForUpdate()
                ->first();

            if ($stock) {
                $stock->decrement('reserved_kg', min($transaction->requested_kg, $stock->reserved_kg));
            }

            $transaction->update(['status' => 'cancelled']);

            DB::commit();

            Notification::sendToUser(
                userId: $transaction->distributor_id,
                tipe: 'alert',
                judul: 'Permintaan pupuk dibatalkan',
                pesan: "Permintaan {$transaction->transaction_number} dibatalkan oleh petani.",
                link: route('distributor.fertilizer.show', $transaction),
            );

            return redirect()->route('farmer.fertilizer.history')->with('success', 'Permintaan pupuk berhasil dibatalkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan permintaan. Coba lagi.');
        }
    }

    private function authorizeFarmerTransaction(FertilizerTransaction $transaction): void
    {
        if ($transaction->farmer_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }
    }
}
