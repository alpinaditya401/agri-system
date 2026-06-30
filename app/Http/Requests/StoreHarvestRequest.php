<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHarvestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_order' => ['required', 'integer', 'min:1'],
            'main_image' => ['nullable', 'string', 'max:255'],
            'origin_province' => ['nullable', 'string', 'max:100'],
            'origin_district' => ['nullable', 'string', 'max:100'],
            'origin_lat' => ['nullable', 'numeric'],
            'origin_lng' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:draft,active,inactive,sold_out'],
        ];
    }
}