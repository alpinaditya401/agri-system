<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\FarmerProfile;
use App\Models\Notification;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FarmerRegistrationController extends Controller
{
    public function create(): View
    {
        $user = Auth::user();
        $user->load('farmerProfile');

        return view('buyer.become-farmer', compact('user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isBuyer(), 403, 'Hanya akun pembeli yang dapat mendaftar sebagai penjual/petani dari halaman ini.');

        $existingProfile = $user->farmerProfile;

        if ($request->has('phone')) {
            $phone = trim((string) $request->input('phone'));
            $request->merge(['phone' => $phone === '' ? null : $phone]);
        }

        $validated = $request->validate([
            'nik' => [
                'nullable',
                'string',
                'size:16',
                'regex:/^\d{16}$/',
                Rule::unique('farmer_profiles', 'nik')->ignore($existingProfile?->id),
            ],
            'farmer_group_id' => ['nullable', 'string', 'max:50'],
            'farmer_group_name' => ['nullable', 'string', 'max:255'],
            'land_area_hectares' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'main_commodity' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'sub_district' => ['required', 'string', 'max:100'],
            'village' => ['required', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ], [
            'nik.size' => 'NIK harus berjumlah tepat 16 digit.',
            'nik.regex' => 'NIK hanya boleh berisi angka.',
            'nik.unique' => 'NIK ini sudah digunakan akun lain.',
            'phone.regex' => 'Nomor HP hanya boleh angka 10-15 digit.',
            'land_area_hectares.required' => 'Luas lahan wajib diisi.',
            'main_commodity.required' => 'Komoditas utama wajib diisi.',
            'province.required' => 'Provinsi lahan wajib dipilih.',
            'district.required' => 'Kabupaten/Kota wajib diisi.',
            'sub_district.required' => 'Kecamatan lahan wajib dipilih.',
            'village.required' => 'Desa/Kelurahan lahan wajib dipilih.',
            'latitude.numeric' => 'Titik lokasi lahan tidak valid.',
            'longitude.numeric' => 'Titik lokasi lahan tidak valid.',
        ]);

        if (blank($validated['nik'] ?? null) && blank($validated['farmer_group_id'] ?? null)) {
            return back()
                ->withErrors(['nik' => 'Isi NIK atau Nomor ID Kelompok Tani untuk mendaftar sebagai petani.'])
                ->withInput();
        }

        DB::transaction(function () use ($user, $validated) {
            $farmerRole = Role::where('name', 'farmer')->firstOrFail();

            $user->update([
                'role_id' => $farmerRole->id,
                'phone' => $validated['phone'] ?? $user->phone,
                'address' => $validated['address'] ?? $user->address,
                'province' => $validated['province'],
                'district' => $validated['district'],
                'sub_district' => $validated['sub_district'],
                'village' => $validated['village'],
                'latitude' => $validated['latitude'] ?? $user->latitude,
                'longitude' => $validated['longitude'] ?? $user->longitude,
            ]);

            FarmerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => $validated['nik'] ?? null,
                    'farmer_group_id' => $validated['farmer_group_id'] ?? null,
                    'farmer_group_name' => $validated['farmer_group_name'] ?? null,
                    'land_area_hectares' => $validated['land_area_hectares'],
                    'main_commodity' => $validated['main_commodity'],
                    'verification_status' => 'pending',
                    'rejection_reason' => null,
                    'verified_at' => null,
                    'verified_by' => null,
                ]
            );
        });

        Notification::sendToAdmins(
            'alert',
            'Pengajuan penjual/petani baru',
            "{$user->name} mengajukan akun sebagai penjual/petani dan menunggu verifikasi.",
            route('admin.farmers.verify.index')
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Pendaftaran sebagai penjual/petani berhasil dikirim. Anda baru bisa login sebagai penjual setelah admin memverifikasi akun.');
    }
}
