<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    /**
     * Get GeoJSON data for farmers and distributors for the GIS Mapping System.
     */
    public function getDistributionPoints()
    {
        // Get all users who have latitude and longitude (Farmers and Distributors)
        $users = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('farmer_profiles', 'users.id', '=', 'farmer_profiles.user_id')
            ->leftJoin('distributor_profiles', 'users.id', '=', 'distributor_profiles.user_id')
            ->whereIn('roles.name', ['farmer', 'distributor'])
            ->whereNotNull('users.latitude')
            ->whereNotNull('users.longitude')
            ->select(
                'users.id',
                'users.name',
                'users.latitude',
                'users.longitude',
                'roles.name as role',
                'farmer_profiles.farmer_group_name',
                'farmer_profiles.main_commodity',
                'distributor_profiles.company_name'
            )
            ->get();

        $features = $users->map(function ($user) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        (float) $user->longitude, 
                        (float) $user->latitude
                    ]
                ],
                'properties' => [
                    'id' => $user->id,
                    'name' => $user->role === 'distributor' && $user->company_name ? $user->company_name : $user->name,
                    'role' => $user->role,
                    'description' => $user->role === 'farmer' 
                        ? 'Kelompok Tani: ' . ($user->farmer_group_name ?? '-') . ' | Komoditas: ' . ($user->main_commodity ?? '-')
                        : 'Distributor Pupuk',
                ]
            ];
        });

        $geoJson = [
            'type' => 'FeatureCollection',
            'features' => $features
        ];

        return response()->json($geoJson);
    }
}
