<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'address'       => ['nullable', 'string'],
            'province'      => ['nullable', 'string', 'max:100'],
            'district'      => ['nullable', 'string', 'max:100'],
            'sub_district'  => ['nullable', 'string', 'max:100'],
            'village'       => ['nullable', 'string', 'max:100'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
        ]);

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
}
