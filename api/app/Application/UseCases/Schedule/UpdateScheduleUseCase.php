<?php

namespace App\Application\UseCases\Schedule;

use App\Application\DTOs\Schedule\UpdateScheduleDTO;
use App\Domain\Entities\Schedule;
use App\Domain\Repositories\ScheduleRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateScheduleUseCase
{
    public function __construct(
        private readonly ScheduleRepositoryInterface $scheduleRepository,
    ) {}

    public function execute(int $scheduleId, int $userId, UpdateScheduleDTO $dto): Schedule
    {
        $schedule = $this->scheduleRepository->findById($scheduleId);

        if (!$schedule || $schedule->userId !== $userId) {
            throw new AuthorizationException('このスケジュールを編集する権限がありません。');
        }

        return $this->scheduleRepository->update($scheduleId, [
            'title' => $dto->title,
            'description' => $dto->description,
            'start_at' => $dto->startAt,
            'end_at' => $dto->endAt,
            'color' => $dto->color,
        ]);
    }
}
