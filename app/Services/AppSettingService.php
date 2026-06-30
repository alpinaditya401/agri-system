<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AppSettingService
{
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            $setting = DB::table('app_settings')->where('key', $key)->first();
        } catch (QueryException) {
            return $default;
        }

        if (!$setting || $setting->value === null || $setting->value === '') {
            return $default;
        }

        if (!$setting->encrypted) {
            return $setting->value;
        }

        try {
            return Crypt::decryptString($setting->value);
        } catch (DecryptException) {
            return $default;
        }
    }

    public function set(string $key, mixed $value, bool $encrypted = false): void
    {
        $storedValue = $value === null || $value === ''
            ? null
            : (string) $value;

        if ($encrypted && filled($storedValue)) {
            $storedValue = Crypt::encryptString($storedValue);
        }

        DB::table('app_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => $storedValue,
                'encrypted' => $encrypted,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function hasFilled(string $key): bool
    {
        return filled($this->get($key));
    }
}
