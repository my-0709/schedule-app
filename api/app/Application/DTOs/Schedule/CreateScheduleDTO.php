<?php

namespace App\Application\DTOs\Schedule;

class CreateScheduleDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $startAt,
        public readonly string $endAt,
        public readonly string $color = 'indigo',
    ) {}
}
