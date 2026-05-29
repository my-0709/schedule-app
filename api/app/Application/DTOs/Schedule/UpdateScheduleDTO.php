<?php

namespace App\Application\DTOs\Schedule;

class UpdateScheduleDTO
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $startAt,
        public readonly string $endAt,
        public readonly string $color = 'indigo',
    ) {}
}
