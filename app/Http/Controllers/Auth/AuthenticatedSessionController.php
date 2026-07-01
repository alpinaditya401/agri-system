<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->has('email')) {
            $request->merge([
                'email' => strtolower(trim((string) $request->input('email'))),
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = $request->user();

            if ($user?->isFarmer() && $user->farmerProfile?->verification_status !== 'verified') {
                Auth::guard('web')->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $status = $user->farmerProfile?->verification_status;
                $message = match ($status) {
                    'pending' => 'Akun penjual/petani Anda sedang menunggu verifikasi admin. Anda baru bisa login setelah disetujui.',
                    'rejected' => 'Akun penjual/petani Anda belum bisa login karena verifikasi ditolak. Hubungi admin untuk memperbarui data.',
                    default => 'Akun penjual/petani Anda belum memiliki data verifikasi yang valid.',
                };

                return back()->withErrors([
                    'email' => $message,
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            $this->forgetApiIntendedUrl($request);

            // The 'dashboard' route will handle redirecting based on the user's role
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function forgetApiIntendedUrl(Request $request): void
    {
        $intended = (string) $request->session()->get('url.intended', '');
        $path = (string) parse_url($intended, PHP_URL_PATH);

        if (Str::startsWith(ltrim($path, '/'), 'api/')) {
            $request->session()->forget('url.intended');
        }
    }
}
