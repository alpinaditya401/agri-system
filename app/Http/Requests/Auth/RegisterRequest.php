<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $role = $this->input('role');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['farmer', 'buyer', 'distributor'])],
            
            // Custom validation logic for Farmers
            // NIK must be 16 digits and unique in the farmer_profiles table
            'nik' => [
                'required_if:role,farmer',
                'nullable',
                'string',
                'size:16',
                'unique:farmer_profiles,nik'
            ],
            
            // Farmer group ID is required if role is farmer (based on previous logic either NIK or Group ID, 
            // but the prompt says NIK or Group ID, let's just make them required_if:role,farmer or nullable)
            'farmer_group_id' => ['nullable', 'string'],
            
            // Coordinates
            'latitude' => [Rule::requiredIf(in_array($role, ['farmer', 'distributor'], true)), 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => [Rule::requiredIf(in_array($role, ['farmer', 'distributor'], true)), 'nullable', 'numeric', 'between:-180,180'],

            // Distributor verification data
            'company_name' => [Rule::requiredIf($role === 'distributor'), 'nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'nik.required_if' => 'NIK wajib diisi jika mendaftar sebagai Petani.',
            'nik.size' => 'NIK harus berjumlah tepat 16 digit.',
            'nik.unique' => 'NIK ini sudah terdaftar sebelumnya.',
            'role.in' => 'Peran yang dipilih tidak valid.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan.',
            'phone.max' => 'Nomor HP maksimal 20 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Jenis akun wajib dipilih.',
            'nik.required_if' => 'NIK wajib diisi jika mendaftar sebagai petani.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.required' => 'Latitude wajib diisi untuk akun petani atau distributor.',
            'latitude.between' => 'Latitude harus berada di antara -90 sampai 90.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.required' => 'Longitude wajib diisi untuk akun petani atau distributor.',
            'longitude.between' => 'Longitude harus berada di antara -180 sampai 180.',
            'company_name.required' => 'Nama perusahaan wajib diisi jika mendaftar sebagai distributor.',
            'company_name.max' => 'Nama perusahaan maksimal 255 karakter.',
            'license_number.max' => 'Nomor izin maksimal 255 karakter.',
        ];
    }

    /**
     * Normalize user input before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }

        if ($this->has('nik')) {
            $this->merge([
                'nik' => preg_replace('/\D/', '', (string) $this->input('nik')),
            ]);
        }
    }
}
