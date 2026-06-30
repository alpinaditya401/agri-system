<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DistributorVerificationController extends Controller
{
    public function index(): View
    {
        $pendingDistributors = User::with('distributorProfile')
            ->whereHas('distributorProfile', fn($query) => $query->where('verification_status', 'pending'))
            ->latest()
            ->paginate(20);

        $verifiedDistributors = User::with('distributorProfile')
            ->whereHas('distributorProfile', fn($query) => $query->where('verification_status', 'verified'))
            ->latest()
            ->paginate(20, ['*'], 'verified_page');

        $rejectedDistributors = User::with('distributorProfile')
            ->whereHas('distributorProfile', fn($query) => $query->where('verification_status', 'rejected'))
            ->latest()
            ->paginate(20, ['*'], 'rejected_page');

        return view('admin.distributors.verify', compact('pendingDistributors', 'verifiedDistributors', 'rejectedDistributors'));
    }

    public function approve(User $distributor): RedirectResponse
    {
        $profile = $distributor->distributorProfile;

        if (! $profile) {
            return back()->with('error', 'Profil distributor tidak ditemukan.');
        }

        $role = Role::where('name', 'distributor')->firstOrFail();

        $distributor->update([
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $profile->update([
            'verification_status' => 'verified',
        ]);

        Notification::sendToUser(
            $distributor->id,
            'info',
            'Pengajuan distributor disetujui',
            'Akun Anda sekarang aktif sebagai distributor pupuk subsidi. Dashboard distributor sudah bisa digunakan.',
            route('distributor.dashboard')
        );

        return back()->with('success', "Pengajuan distributor {$distributor->name} berhasil disetujui.");
    }

    public function reject(Request $request, User $distributor): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $profile = $distributor->distributorProfile;

        if (! $profile) {
            return back()->with('error', 'Profil distributor tidak ditemukan.');
        }

        $profile->update([
            'verification_status' => 'rejected',
        ]);

        Notification::sendToUser(
            $distributor->id,
            'alert',
            'Pengajuan distributor ditolak',
            'Pengajuan distributor Anda ditolak. Alasan: ' . $validated['rejection_reason'],
            route('buyer.become-distributor.create')
        );

        return back()->with('success', "Pengajuan distributor {$distributor->name} berhasil ditolak.");
    }
}
