<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id ?? null,
            'en_name' => $this->en_name ?? null,
            'ar_name' => $this->ar_name ?? null,
            'en_description' => $this->en_description ?? null,
            'ar_description' => $this->ar_description ?? null,
            'image' => $this->image ? asset(str_replace('//', '/', $this->image)) : null,
            'slug' => $this->slug ?? null,
            'website_url' => $this->website_url ?? null,
            'status' => $this->status ?? null,

            'category_id' => $this->category_id ?? null,
            'category_en_name' => $this->whenLoaded('category', fn () => $this->category?->en_name),
            'category_ar_name' => $this->whenLoaded('category', fn () => $this->category?->ar_name),

            'created_by' => $this->created_by ?? null,
            'updated_by' => $this->updated_by ?? null,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s') ?? null,
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s') ?? null,
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s') ?? null,

            // 'place_branches' => PlaceBrancheResource::collection($this->whenLoaded('branches')),
            'place_reviews' => ReviewResource::collection($this->whenLoaded('reviews')),

        ];
    }
}
