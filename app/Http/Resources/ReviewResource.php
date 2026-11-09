<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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

            'user_id' => $this->user_id ?? null,
            'full_name' => $this->user?->full_name ?? null,
            'user_name' => $this->user?->user_name ?? null,
            'avatar' =>  asset(str_replace('//', '/', $this->user?->avatar)) ?? null,

            'place_id' => $this->place_id ?? null,

            'comment' => $this->comment ?? null,
            'rating' => $this->rating ?? null,

            'created_by' => $this->created_by ?? null,
            'updated_by' => $this->updated_by ?? null,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s') ?? null,
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s') ?? null,
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s') ?? null,
        ];
    }
}
