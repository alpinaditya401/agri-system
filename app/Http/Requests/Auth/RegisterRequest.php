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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
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
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
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
            'email.unique' => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }
}
