<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductResource;
class CategoryResource extends JsonResource
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
            'description' => $this->description ?? null,
            'image' =>  $this->image ? asset(str_replace('//', '/', $this->image)) : null,
            'icone' => $this->icone ?? null,
            'slug' => $this->slug ?? null,
            'sort' => $this->sort ?? null,
            'status' => $this->status ?? null,

            'created_by' => $this->created_by ?? null,
            'updated_by' => $this->updated_by ?? null,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s') ?? null,
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s') ?? null,
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s') ?? null,
            'places' => PlaceResource::collection($this->whenLoaded('places')),
        ];
    }
}
