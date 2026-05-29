<?php

namespace App\Application\UseCases\Schedule;

use App\Domain\Repositories\ScheduleRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;

class DeleteScheduleUseCase
{
    public function __construct(
        private readonly ScheduleRepositoryInterface $scheduleRepository,
    ) {}

    public function execute(int $scheduleId, int $userId): void
    {
        $schedule = $this->scheduleRepository->findById($scheduleId);

        if (!$schedule || $schedule->userId !== $userId) {
            throw new AuthorizationException('このスケジュールを削除する権限がありません。');
        }

        $this->scheduleRepository->delete($scheduleId);
    }
}
