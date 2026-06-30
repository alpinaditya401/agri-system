<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardRegion
{
    public static function fromRequest(Request $request): array
    {
        return [
            'province' => trim((string) $request->query('province', '')),
            'district' => trim((string) $request->query('district', '')),
        ];
    }

    public static function options(array $filters = []): array
    {
        $province = $filters['province'] ?? '';

        return [
            'provinces' => self::distinctUserColumn('province'),
            'districts' => self::distinctUserColumn('district', $province),
        ];
    }

    public static function label(array $filters): string
    {
        $parts = array_filter([
            $filters['district'] ?? '',
            $filters['province'] ?? '',
        ]);

        return $parts ? implode(', ', $parts) : 'Semua wilayah';
    }

    public static function hasFilter(array $filters): bool
    {
        return filled($filters['province'] ?? null) || filled($filters['district'] ?? null);
    }

    public static function applyUser($query, array $filters)
    {
        if (filled($filters['province'] ?? null)) {
            $query->where('province', 'like', '%' . $filters['province'] . '%');
        }

        if (filled($filters['district'] ?? null)) {
            $query->where('district', 'like', '%' . $filters['district'] . '%');
        }

        return $query;
    }

    public static function applyRelatedUser($query, string $relation, array $filters)
    {
        if (! self::hasFilter($filters)) {
            return $query;
        }

        return $query->whereHas($relation, fn($userQuery) => self::applyUser($userQuery, $filters));
    }

    public static function applyProduct($query, array $filters)
    {
        if (filled($filters['province'] ?? null)) {
            $province = $filters['province'];
            $query->where(function ($builder) use ($province) {
                $builder->where('origin_province', 'like', '%' . $province . '%')
                    ->orWhereHas('farmer', fn($userQuery) => $userQuery->where('province', 'like', '%' . $province . '%'));
            });
        }

        if (filled($filters['district'] ?? null)) {
            $district = $filters['district'];
            $query->where(function ($builder) use ($district) {
                $builder->where('origin_district', 'like', '%' . $district . '%')
                    ->orWhereHas('farmer', fn($userQuery) => $userQuery->where('district', 'like', '%' . $district . '%'));
            });
        }

        return $query;
    }

    public static function filterCommodityPrices(Collection $prices, array $filters): Collection
    {
        if (! self::hasFilter($filters)) {
            return $prices;
        }

        return $prices->filter(function ($price) use ($filters) {
            $region = Str::lower((string) ($price->region ?? ''));
            $provinceMatch = blank($filters['province'] ?? '') || Str::contains($region, Str::lower($filters['province']));
            $districtMatch = blank($filters['district'] ?? '') || Str::contains($region, Str::lower($filters['district']));

            return $provinceMatch && $districtMatch;
        })->values();
    }

    private static function distinctUserColumn(string $column, ?string $province = null): Collection
    {
        return User::query()
            ->when($province && $column === 'district', fn($query) => $query->where('province', 'like', '%' . $province . '%'))
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->filter()
            ->values();
    }
}
