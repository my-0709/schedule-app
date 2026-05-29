<?php

namespace App\Presentation\Http\Resources;

use App\Domain\Entities\Schedule;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function __construct(private readonly Schedule $schedule) {}

    public function toArray($request): array
    {
        return [
            'id' => $this->schedule->id,
            'user_id' => $this->schedule->userId,
            'title' => $this->schedule->title,
            'description' => $this->schedule->description,
            'start_at' => $this->schedule->startAt->format('Y-m-d\TH:i:s'),
            'end_at' => $this->schedule->endAt->format('Y-m-d\TH:i:s'),
            'color' => $this->schedule->color,
            'created_at' => $this->schedule->createdAt?->format('Y-m-d\TH:i:s'),
            'updated_at' => $this->schedule->updatedAt?->format('Y-m-d\TH:i:s'),
        ];
    }
}
