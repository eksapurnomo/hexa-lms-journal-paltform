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
            'id'              => $this->id,
            'phone'           => $this->phone,
            'email'           => $this->email,
            'name'            => $this->name,
            'profile_picture' => $this->profilePicturePath,
            'is_active'       => $this->is_active,
            'is_admin'        => $this->is_admin,
        ];
    }
}
