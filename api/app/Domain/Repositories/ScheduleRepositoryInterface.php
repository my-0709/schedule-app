<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Schedule;

interface ScheduleRepositoryInterface
{
    public function findById(int $id): ?Schedule;
    public function findByUserId(int $userId): array;
    public function save(Schedule $schedule): Schedule;
    public function update(int $id, array $data): Schedule;
    public function delete(int $id): void;
}
