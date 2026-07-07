<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['required', 'regex:/^[0-9]{10,15}$/'],
            'name' => ['required', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'sub_district' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:255'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'phone.required' => 'No. HP wajib diisi.',
            'phone.regex' => 'No. HP hanya boleh angka, 10 sampai 15 digit.',
            'name.required' => 'Nama lengkap wajib diisi.',
        ]);

        $user = User::with('role')->where('email', $validated['email'])->first();

        if (! $user || ! $this->identityMatches($user, $validated)) {
            throw ValidationException::withMessages([
                'email' => 'Data verifikasi tidak cocok. Pastikan email, no. HP, nama, dan lokasi sesuai dengan data akun.',
            ]);
        }

        if ($this->requiresLocation($user) && ! $this->locationMatches($user, $validated)) {
            throw ValidationException::withMessages([
                'province' => 'Untuk akun Petani atau Distributor, data lokasi wajib diisi dan harus sesuai dengan data akun.',
            ]);
        }

        $token = Password::broker()->createToken($user);
        $resetLink = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        return back()
            ->withInput($request->only('email', 'phone', 'name', 'province', 'district', 'sub_district', 'village'))
            ->with('status', 'Data berhasil diverifikasi. Gunakan link reset password di bawah ini.')
            ->with('reset_link', $resetLink);
    }

    private function identityMatches(User $user, array $data): bool
    {
        return $this->normalizePhone($user->phone) === $this->normalizePhone($data['phone'] ?? '')
            && $this->normalizeText($user->name) === $this->normalizeText($data['name'] ?? '');
    }

    private function requiresLocation(User $user): bool
    {
        return in_array($user->role?->name, ['farmer', 'distributor'], true);
    }

    private function locationMatches(User $user, array $data): bool
    {
        foreach (['province', 'district', 'sub_district', 'village'] as $field) {
            if (! filled($data[$field] ?? null)) {
                return false;
            }

            if ($this->normalizeLocation($user->{$field}) !== $this->normalizeLocation($data[$field])) {
                return false;
            }
        }

        return true;
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';

        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            return '0' . $digits;
        }

        return $digits;
    }

    private function normalizeText(?string $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->squish()
            ->toString();
    }

    private function normalizeLocation(?string $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->replaceMatches('/\b(kabupaten|kab\.|kota administrasi|kota|administrasi)\b/u', '')
            ->replaceMatches('/[^a-z0-9]+/u', ' ')
            ->squish()
            ->toString();
    }
}
