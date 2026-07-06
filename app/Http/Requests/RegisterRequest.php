<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

/**
 * Registration Form Request
 *
 * Applies conditional validation rules based on the chosen role:
 *  - farmer: requires NIK (16-digit numeric) OR farmer_group_id, plus coordinates
 *  - distributor: requires coordinates, company_name
 *  - buyer: only standard fields required
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = $this->input('role');

        return [
            // ── Standard fields (all roles) ──────────────────────────────────
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role'     => ['required', Rule::in(['farmer', 'buyer', 'distributor'])],
            'phone'    => ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address'  => ['nullable', 'string', 'max:500'],
            'province'     => ['nullable', 'string', 'max:100'],
            'district'     => ['nullable', 'string', 'max:100'],
            'sub_district' => ['nullable', 'string', 'max:100'],
            'village'      => ['nullable', 'string', 'max:100'],

            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // ── Farmer-only fields ────────────────────────────────────────────
            // At least one of nik OR farmer_group_id must be present for farmers.
            // This is enforced via withValidator() below.
            'nik' => [
                Rule::requiredIf($role === 'farmer' && !$this->input('farmer_group_id')),
                'nullable',
                'string',
                'size:16',              // NIK is always exactly 16 digits
                'regex:/^\d{16}$/',     // must be numeric only
                'unique:farmer_profiles,nik',
            ],
            'farmer_group_id' => [
                Rule::requiredIf($role === 'farmer' && !$this->input('nik')),
                'nullable',
                'string',
                'max:50',
            ],
            'farmer_group_name'  => ['nullable', 'string', 'max:255'],
            'land_area_hectares' => ['nullable', 'numeric', 'min:0.01', 'max:9999.99'],
            'main_commodity'     => ['nullable', 'string', 'max:100'],

            // ── Distributor-only fields ───────────────────────────────────────
            'company_name'   => [
                Rule::requiredIf($role === 'distributor'),
                'nullable', 'string', 'max:255',
            ],
            'license_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Additional cross-field validation for farmers:
     * NIK OR farmer_group_id must be present — not both can be empty.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('role') === 'farmer') {
                $hasNik     = !empty(trim($this->input('nik', '')));
                $hasGroupId = !empty(trim($this->input('farmer_group_id', '')));

                if (!$hasNik && !$hasGroupId) {
                    $validator->errors()->add(
                        'nik',
                        'Petani wajib mengisi NIK (16 digit) atau Nomor ID Kelompok Tani untuk verifikasi subsidi.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'nik.size'    => 'NIK harus terdiri dari tepat 16 digit angka.',
            'nik.regex'   => 'NIK hanya boleh berisi angka (0-9).',
            'nik.unique'  => 'NIK ini sudah terdaftar di sistem.',
            'latitude.numeric' => 'Titik lokasi tidak valid.',
            'longitude.numeric' => 'Titik lokasi tidak valid.',
            'latitude.between'  => 'Titik lokasi tidak valid.',
            'longitude.between' => 'Titik lokasi tidak valid.',
            'role.in' => 'Peran tidak valid. Pilih: Petani, Pembeli, atau Distributor.',
            'phone.regex' => 'Nomor HP hanya boleh angka 10-15 digit.',
            'company_name.required_if' => 'Nama perusahaan wajib diisi untuk distributor.',
        ];
    }

    /**
     * Sanitize inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('nik')) {
            $this->merge(['nik' => preg_replace('/\D/', '', $this->input('nik'))]);
        }
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
        if ($this->has('phone')) {
            $phone = trim((string) $this->input('phone'));
            $this->merge(['phone' => $phone === '' ? null : $phone]);
        }
    }
}
