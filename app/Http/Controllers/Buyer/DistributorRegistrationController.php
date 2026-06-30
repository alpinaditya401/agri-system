<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\DistributorProfile;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DistributorRegistrationController extends Controller
{
    public function create(): View
    {
        $user = Auth::user()->load('distributorProfile');

        return view('buyer.become-distributor', compact('user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isBuyer(), 403, 'Hanya akun pembeli yang dapat mengajukan sebagai distributor.');

        $existingProfile = $user->distributorProfile;

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'license_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('distributor_profiles', 'license_number')->ignore($existingProfile?->id),
            ],
            'storage_capacity_kg' => ['required', 'integer', 'min:100', 'max:10000000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'province' => ['nullable', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'sub_district' => ['nullable', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'company_name.required' => 'Nama usaha distributor wajib diisi.',
            'license_number.required' => 'Nomor izin distributor wajib diisi.',
            'license_number.unique' => 'Nomor izin ini sudah digunakan distributor lain.',
            'storage_capacity_kg.required' => 'Kapasitas gudang wajib diisi.',
            'storage_capacity_kg.min' => 'Kapasitas gudang minimal 100 kg.',
            'address.required' => 'Alamat gudang wajib diisi.',
            'district.required' => 'Kabupaten/Kota wajib diisi.',
            'latitude.required' => 'Latitude gudang wajib diisi.',
            'longitude.required' => 'Longitude gudang wajib diisi.',
        ]);

        $user->update([
            'phone' => $validated['phone'] ?? $user->phone,
            'address' => $validated['address'],
            'province' => $validated['province'] ?? $user->province,
            'district' => $validated['district'],
            'sub_district' => $validated['sub_district'] ?? $user->sub_district,
            'village' => $validated['village'] ?? $user->village,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        DistributorProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => $validated['company_name'],
                'license_number' => $validated['license_number'],
                'storage_capacity_kg' => $validated['storage_capacity_kg'],
                'verification_status' => 'pending',
            ]
        );

        Notification::sendToAdmins(
            'alert',
            'Pengajuan distributor baru',
            "{$user->name} mengajukan akun sebagai distributor pupuk subsidi.",
            route('admin.distributors.verify.index')
        );

        return redirect()
            ->route('buyer.become-distributor.create')
            ->with('success', 'Pengajuan distributor berhasil dikirim. Admin akan meninjau dan Anda akan menerima notifikasi hasilnya.');
    }
}
