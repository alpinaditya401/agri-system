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
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'province' => ['nullable', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'sub_district' => ['nullable', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'nik.size' => 'NIK harus berjumlah tepat 16 digit.',
            'nik.regex' => 'NIK hanya boleh berisi angka.',
            'nik.unique' => 'NIK ini sudah digunakan akun lain.',
            'land_area_hectares.required' => 'Luas lahan wajib diisi.',
            'main_commodity.required' => 'Komoditas utama wajib diisi.',
            'district.required' => 'Kabupaten/Kota wajib diisi.',
            'latitude.required' => 'Latitude lahan wajib diisi.',
            'longitude.required' => 'Longitude lahan wajib diisi.',
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
                'province' => $validated['province'] ?? $user->province,
                'district' => $validated['district'],
                'sub_district' => $validated['sub_district'] ?? $user->sub_district,
                'village' => $validated['village'] ?? $user->village,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
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

        return redirect()
            ->route('farmer.dashboard')
            ->with('success', 'Pendaftaran sebagai penjual/petani berhasil dikirim. Profil petani Anda menunggu verifikasi admin.');
    }
}
