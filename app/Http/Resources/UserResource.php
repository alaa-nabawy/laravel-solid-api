<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id'                => $this->resource->id,
            'name'              => $this->resource->name,
            'email'             => $this->resource->email,
            'email_verified_at' => $this->resource->email_verified_at?->toISOString(),
            'created_at'        => $this->resource->created_at->toISOString(),
            'updated_at'        => $this->resource->updated_at->toISOString(),
        ];

        // Conditionally include sensitive data
        if ($request->user()?->id === $this->resource->id) {
            $data['email_verified'] = ! is_null($this->resource->email_verified_at);
        }

        return $data;
    }
}
