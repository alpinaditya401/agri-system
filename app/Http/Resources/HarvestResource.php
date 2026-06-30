<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HarvestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,

            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ]),

            'farmer' => $this->whenLoaded('farmer', fn() => [
                'id' => $this->farmer?->id,
                'name' => $this->farmer?->name,
                'email' => $this->farmer?->email,
            ]),

            'price_per_unit' => $this->price_per_unit,
            'unit' => $this->unit,
            'stock_quantity' => $this->stock_quantity,
            'minimum_order' => $this->minimum_order,
            'main_image' => $this->main_image,

            'origin' => [
                'province' => $this->origin_province,
                'district' => $this->origin_district,
                'latitude' => $this->origin_lat,
                'longitude' => $this->origin_lng,
            ],

            'status' => $this->status,
            'is_featured' => (bool) $this->is_featured,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}