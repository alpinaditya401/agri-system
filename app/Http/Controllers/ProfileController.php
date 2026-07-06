<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();
        $user->load(['farmerProfile', 'distributorProfile']);

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($request->has('phone')) {
            $phone = trim((string) $request->input('phone'));
            $request->merge(['phone' => $phone === '' ? null : $phone]);
        }

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_profile_photo' => ['nullable', 'boolean'],
            'phone'         => ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address'       => ['nullable', 'string'],
            'province'      => ['nullable', 'string', 'max:100'],
            'district'      => ['nullable', 'string', 'max:100'],
            'sub_district'  => ['nullable', 'string', 'max:100'],
            'village'       => ['nullable', 'string', 'max:100'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
        ], [
            'phone.regex' => 'Nomor HP hanya boleh angka 10-15 digit.',
        ]);

        unset($validated['profile_photo'], $validated['remove_profile_photo']);

        if ($request->boolean('remove_profile_photo') && ! $request->hasFile('profile_photo')) {
            $this->deleteStoredProfilePhoto($user->profile_photo_path);
            $validated['profile_photo_path'] = null;
        }

        if ($request->hasFile('profile_photo')) {
            $this->deleteStoredProfilePhoto($user->profile_photo_path);
            $validated['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user->update($validated);

        // Update role-specific profile data if provided
        if ($user->isFarmer() && $user->farmerProfile) {
            $farmerData = $request->validate([
                'land_area_hectares' => ['nullable', 'numeric'],
                'main_commodity'     => ['nullable', 'string', 'max:100'],
                'farmer_group_name'  => ['nullable', 'string', 'max:255'],
            ]);
            $user->farmerProfile->update($farmerData);
        }

        if ($user->isDistributor() && $user->distributorProfile) {
            $distData = $request->validate([
                'company_name' => ['nullable', 'string', 'max:255'],
            ]);
            $user->distributorProfile->update($distData);
        }

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    private function deleteStoredProfilePhoto(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://', '/', 'storage/', 'images/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
