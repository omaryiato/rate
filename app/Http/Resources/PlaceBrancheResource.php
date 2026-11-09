<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaceBrancheResource extends JsonResource
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
            'sku' => $this->sku ?? null,
            'stock' => $this->stock ?? null,
            'price' => $this->price ?? null,
            'status' => $this->status ?? null,

            'created_by' => $this->created_by ?? null,
            'updated_by' => $this->updated_by ?? null,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s') ?? null,
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s') ?? null,
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s') ?? null,

            'product_id' => $this->product_id ?? null,

            'color_id' => $this->color_id ?? null,
            'color_en_name' => $this->color?->en_meaning ?? null,
            'color_ar_name' => $this->color?->ar_meaning ?? null,

            'size_id' => $this->size_id ?? null,
            'size_en_name' => $this->size?->en_meaning ?? null,
            'size_ar_name' => $this->size?->ar_meaning ?? null,

            'material_id' => $this->material_id ?? null,
            'material_en_name' => $this->material?->en_meaning ?? null,
            'material_ar_name' => $this->material?->ar_meaning ?? null,

            'stone_id' => $this->stone_id ?? null,
            'stone_en_name' => $this->stone?->en_meaning ?? null,
            'stone_ar_name' => $this->stone?->ar_meaning ?? null,
        ];
    }
}
