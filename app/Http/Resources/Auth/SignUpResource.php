<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignUpResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'user_name' => $this->user_name,
            'phone_number' => $this->phone_number,
            'status' => (int) $this->status,
            'type' => (int) $this->type,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
