<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\FertilizerStock;
use App\Models\FertilizerTransaction;
use App\Models\Notification;
use App\Services\FertilizerQuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        Notification::sendToUser(
            userId: $transaction->farmer_id,
            tipe: 'info',
            judul: 'Pengajuan pupuk disetujui',
            pesan: "Permintaan {$transaction->transaction_number} disetujui sebanyak {$approvedKg} kg. Pantau status penyerahan dari halaman riwayat pupuk.",
            link: route('farmer.fertilizer.transactions.show', $transaction),
        );

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

        Notification::sendToUser(
            userId: $transaction->farmer_id,
            tipe: 'alert',
            judul: 'Pengajuan pupuk ditolak',
            pesan: "Permintaan {$transaction->transaction_number} ditolak. Alasan: {$validated['rejection_reason']}",
            link: route('farmer.fertilizer.transactions.show', $transaction),
        );

        return back()->with('success', 'Permintaan pupuk ditolak.');
    }

    public function redirectDispense(FertilizerTransaction $transaction): RedirectResponse
    {
        $this->authorizeDistributorTransaction($transaction);

        return redirect()
            ->route('distributor.fertilizer.show', $transaction)
            ->with('error', 'Gunakan tombol "Tandai Diserahkan" dari halaman detail transaksi agar prosesnya aman.');
    }

    public function dispense(FertilizerTransaction $transaction): RedirectResponse
    {
        $this->authorizeDistributorTransaction($transaction);

        if ($transaction->status !== 'approved') {
            return back()->with('error', 'Transaksi harus disetujui terlebih dahulu sebelum diserahkan.');
        }

        $approvedKg = (int) ($transaction->approved_kg ?? $transaction->requested_kg);

        if ($approvedKg < 1) {
            return back()->with('error', 'Jumlah pupuk yang disetujui tidak valid.');
        }

        try {
            DB::transaction(function () use ($transaction, $approvedKg) {
                $lockedTransaction = FertilizerTransaction::whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedTransaction->status !== 'approved') {
                    throw new \RuntimeException('Transaksi sudah tidak dapat ditandai diserahkan.');
                }

                $stock = FertilizerStock::where('distributor_id', Auth::id())
                    ->where('fertilizer_type_id', $lockedTransaction->fertilizer_type_id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    throw new \RuntimeException('Stok distributor untuk jenis pupuk ini tidak ditemukan.');
                }

                if ((int) $stock->stock_kg < $approvedKg) {
                    throw new \RuntimeException("Stok tidak mencukupi. Stok saat ini: {$stock->stock_kg} kg.");
                }

                $reservedToRelease = min((int) $lockedTransaction->requested_kg, (int) $stock->reserved_kg);

                $stock->update([
                    'stock_kg' => max(0, (int) $stock->stock_kg - $approvedKg),
                    'reserved_kg' => max(0, (int) $stock->reserved_kg - $reservedToRelease),
                ]);

                $this->quotaService->markAsUsed((int) $lockedTransaction->fertilizer_quota_id, $approvedKg);

                $lockedTransaction->update([
                    'status'       => 'dispensed',
                    'dispensed_at' => now(),
                    'processed_by' => Auth::id(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Failed to dispense fertilizer transaction', [
                'transaction_id' => $transaction->id,
                'distributor_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Pupuk belum bisa ditandai diserahkan. Cek stok, kuota, lalu coba lagi.');
        }

        $transaction->refresh();

        Notification::sendToUser(
            userId: $transaction->farmer_id,
            tipe: 'pengiriman',
            judul: 'Pupuk diserahkan',
            pesan: "Pupuk untuk transaksi {$transaction->transaction_number} sudah ditandai diserahkan oleh distributor.",
            link: route('farmer.fertilizer.transactions.show', $transaction),
        );

        return back()->with('success', 'Pupuk berhasil diserahkan ke petani.');
    }

    private function authorizeDistributorTransaction(FertilizerTransaction $transaction): void
    {
        if ($transaction->distributor_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Transaksi ini bukan milik Anda.');
        }
    }
}
