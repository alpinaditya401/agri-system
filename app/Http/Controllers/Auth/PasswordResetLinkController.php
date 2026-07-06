<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->has('email')) {
            $request->merge([
                'email' => strtolower(trim((string) $request->input('email'))),
            ]);
        }

        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', $this->statusMessage($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => $this->statusMessage($status)]);
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            Password::RESET_LINK_SENT => 'Link reset password berhasil dikirim. Silakan cek email Anda.',
            Password::INVALID_USER => 'Email tidak ditemukan di sistem Agrilink.',
            Password::RESET_THROTTLED => 'Terlalu banyak permintaan. Tunggu beberapa saat sebelum mencoba lagi.',
            default => 'Link reset password belum bisa dikirim. Coba lagi beberapa saat.',
        };
    }
}
