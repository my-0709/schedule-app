<?php

namespace App\Application\UseCases\Schedule;

use App\Domain\Repositories\ScheduleRepositoryInterface;

class GetSchedulesUseCase
{
    public function __construct(
        private readonly ScheduleRepositoryInterface $scheduleRepository,
    ) {}

    public function execute(int $userId): array
    {
        return $this->scheduleRepository->findByUserId($userId);
    }
}
