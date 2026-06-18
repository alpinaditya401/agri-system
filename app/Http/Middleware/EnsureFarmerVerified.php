<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFarmerVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role?->name !== 'farmer') {
            abort(403, 'Akses hanya untuk petani.');
        }

        $profile = $user->farmerProfile;

        if (!$profile) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Lengkapi profil petani Anda terlebih dahulu.');
        }

        if ($profile->verification_status === 'pending') {
            return redirect()->route('farmer.dashboard')
                ->with('info', 'Profil Anda sedang dalam proses verifikasi oleh admin. '
                             . 'Anda akan mendapat akses ke fitur pupuk bersubsidi setelah terverifikasi.');
        }

        if ($profile->verification_status === 'rejected') {
            return redirect()->route('farmer.dashboard')
                ->with('error', 'Verifikasi profil Anda ditolak. '
                              . 'Alasan: ' . ($profile->rejection_reason ?? 'Tidak disebutkan') . '. '
                              . 'Hubungi admin untuk informasi lebih lanjut.');
        }

        return $next($request);
    }
}
