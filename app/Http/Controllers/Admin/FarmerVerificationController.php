<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmerProfile;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmerVerificationController extends Controller
{
    /**
     * List farmers pending verification.
     */
    public function index(): View
    {
        $pendingFarmers = User::with('farmerProfile')
            ->whereHas('role', fn($q) => $q->where('name', 'farmer'))
            ->whereHas('farmerProfile', fn($q) => $q->where('verification_status', 'pending'))
            ->latest()
            ->paginate(20);

        $verifiedFarmers = User::with('farmerProfile')
            ->whereHas('role', fn($q) => $q->where('name', 'farmer'))
            ->whereHas('farmerProfile', fn($q) => $q->where('verification_status', 'verified'))
            ->latest()
            ->paginate(20, ['*'], 'verified_page');

        $rejectedFarmers = User::with('farmerProfile')
            ->whereHas('role', fn($q) => $q->where('name', 'farmer'))
            ->whereHas('farmerProfile', fn($q) => $q->where('verification_status', 'rejected'))
            ->latest()
            ->paginate(20, ['*'], 'rejected_page');

        return view('admin.farmers.verify', compact('pendingFarmers', 'verifiedFarmers', 'rejectedFarmers'));
    }

    /**
     * Approve a farmer's verification.
     */
    public function approve(User $farmer): RedirectResponse
    {
        $profile = $farmer->farmerProfile;

        if (!$profile) {
            return back()->with('error', 'Profil petani tidak ditemukan.');
        }

        $profile->update([
            'verification_status' => 'verified',
            'verified_at'         => now(),
            'verified_by'         => auth()->id(),
        ]);

        Notification::sendToUser(
            $farmer->id,
            'info',
            'Pengajuan penjual disetujui',
            'Akun Anda sudah diverifikasi sebagai penjual/petani. Anda sekarang bisa mengelola produk, pesanan, dan pengajuan pupuk.',
            route('farmer.dashboard')
        );

        return back()->with('success', "Petani {$farmer->name} berhasil diverifikasi.");
    }

    /**
     * Reject a farmer's verification.
     */
    public function reject(Request $request, User $farmer): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $profile = $farmer->farmerProfile;

        if (!$profile) {
            return back()->with('error', 'Profil petani tidak ditemukan.');
        }

        $profile->update([
            'verification_status' => 'rejected',
            'rejection_reason'    => $request->input('rejection_reason'),
            'verified_by'         => auth()->id(),
        ]);

        Notification::sendToUser(
            $farmer->id,
            'alert',
            'Pengajuan penjual ditolak',
            'Pengajuan penjual/petani Anda ditolak. Alasan: ' . $request->input('rejection_reason'),
            route('farmer.dashboard')
        );

        return back()->with('success', "Verifikasi petani {$farmer->name} ditolak.");
    }
}
