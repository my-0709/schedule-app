<?php

namespace App\Presentation\Http\Resources;

use App\Domain\Entities\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function __construct(private readonly User $user) {}

    public function toArray($request): array
    {
        return [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'avatar' => $this->user->avatar
                ? Storage::disk('public')->url($this->user->avatar)
                : null,
        ];
    }
}
