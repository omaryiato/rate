<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'full_name' => $this->full_name ?? null,
            'user_name' => $this->user_name ?? null,
            'phone_number' => $this->phone_number ?? null,
            'email_address' => $this->email_address ?? null,
            'status' => $this->status ?? null,
            'type' => $this->type === null ? null : (int) $this->type,
            'avatar' => $this->avatar ? asset(str_replace('//', '/', $this->avatar)) : null,
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s') ?? null,
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s') ?? null,
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s') ?? null,
        ];
    }
}
