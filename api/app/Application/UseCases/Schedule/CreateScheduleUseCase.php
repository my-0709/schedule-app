<?php

namespace App\Application\UseCases\Schedule;

use App\Application\DTOs\Schedule\CreateScheduleDTO;
use App\Domain\Entities\Schedule;
use App\Domain\Repositories\ScheduleRepositoryInterface;

class CreateScheduleUseCase
{
    public function __construct(
        private readonly ScheduleRepositoryInterface $scheduleRepository,
    ) {}

    public function execute(CreateScheduleDTO $dto): Schedule
    {
        $schedule = new Schedule(
            id: null,
            userId: $dto->userId,
            title: $dto->title,
            description: $dto->description,
            startAt: new \DateTimeImmutable($dto->startAt),
            endAt: new \DateTimeImmutable($dto->endAt),
            color: $dto->color,
        );

        return $this->scheduleRepository->save($schedule);
    }
}
