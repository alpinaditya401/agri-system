<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\FarmerProfile;
use App\Models\DistributorProfile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Only expose registerable roles (not admin — admin created via seeder/console)
        $roles = Role::whereIn('name', ['farmer', 'buyer', 'distributor'])
                     ->orderBy('display_name')
                     ->get();

        return view('auth.register', compact('roles'));
    }

    /**
     * Handle an incoming registration request.
     *
     * Business Rules:
     * - All roles require standard user fields
     * - Farmer role: REQUIRES either NIK (16 digits) OR a valid Farmer Group ID
     * - Farmer role: Coordinates (lat/lng) are required
     * - Distributor role: Coordinates (lat/lng) are required
     * - Farmer profiles start as 'pending' verification — admin must verify before
     *   the farmer can access subsidy quota
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $role = Role::where('name', $validated['role'])->firstOrFail();

            // --- Create the base user ---
            $user = User::create([
                'role_id'       => $role->id,
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'password'      => Hash::make($validated['password']),
                'phone'         => $validated['phone'] ?? null,
                'address'       => $validated['address'] ?? null,
                'province'      => $validated['province'] ?? null,
                'district'      => $validated['district'] ?? null,
                'sub_district'  => $validated['sub_district'] ?? null,
                'village'       => $validated['village'] ?? null,
                'latitude'      => $validated['latitude'] ?? null,
                'longitude'     => $validated['longitude'] ?? null,
            ]);

            // --- Create role-specific profiles ---
            if ($role->name === 'farmer') {
                $this->createFarmerProfile($user, $validated);
            } elseif ($role->name === 'distributor') {
                $this->createDistributorProfile($user, $validated);
            }

            DB::commit();

            event(new Registered($user));
            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('status', 'Registrasi berhasil! Profil Anda sedang dalam proses verifikasi.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Registration failed', ['error' => $e->getMessage(), 'email' => $validated['email']]);
            return back()->withErrors(['register' => 'Terjadi kesalahan saat registrasi. Coba lagi.'])
                         ->withInput();
        }
    }

    /**
     * Create farmer profile.
     * NIK format: exactly 16 numeric digits (Indonesian standard).
     * Either NIK or farmer_group_id must be provided — validated in RegisterRequest.
     */
    private function createFarmerProfile(User $user, array $data): FarmerProfile
    {
        return FarmerProfile::create([
            'user_id'             => $user->id,
            'nik'                 => $data['nik'] ?? null,
            'farmer_group_id'     => $data['farmer_group_id'] ?? null,
            'farmer_group_name'   => $data['farmer_group_name'] ?? null,
            'land_area_hectares'  => $data['land_area_hectares'] ?? null,
            'main_commodity'      => $data['main_commodity'] ?? null,
            'verification_status' => 'pending', // always starts pending
        ]);
    }

    /**
     * Create distributor profile.
     */
    private function createDistributorProfile(User $user, array $data): DistributorProfile
    {
        return DistributorProfile::create([
            'user_id'          => $user->id,
            'company_name'     => $data['company_name'] ?? null,
            'license_number'   => $data['license_number'] ?? null,
            'verification_status' => 'pending',
        ]);
    }
}
