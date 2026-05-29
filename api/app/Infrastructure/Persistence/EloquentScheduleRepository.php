<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Schedule as ScheduleEntity;
use App\Domain\Repositories\ScheduleRepositoryInterface;
use App\Infrastructure\Models\Schedule as ScheduleModel;

class EloquentScheduleRepository implements ScheduleRepositoryInterface
{
    public function findById(int $id): ?ScheduleEntity
    {
        $model = ScheduleModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByUserId(int $userId): array
    {
        return ScheduleModel::where('user_id', $userId)
            ->orderBy('start_at')
            ->get()
            ->map(fn($model) => $this->toEntity($model))
            ->toArray();
    }

    public function save(ScheduleEntity $schedule): ScheduleEntity
    {
        $model = ScheduleModel::create([
            'user_id' => $schedule->userId,
            'title' => $schedule->title,
            'description' => $schedule->description,
            'start_at' => $schedule->startAt->format('Y-m-d H:i:s'),
            'end_at' => $schedule->endAt->format('Y-m-d H:i:s'),
            'color' => $schedule->color,
        ]);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): ScheduleEntity
    {
        $model = ScheduleModel::findOrFail($id);
        $model->update($data);
        $model->refresh();

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        ScheduleModel::findOrFail($id)->delete();
    }

    private function toEntity(ScheduleModel $model): ScheduleEntity
    {
        return new ScheduleEntity(
            id: $model->id,
            userId: $model->user_id,
            title: $model->title,
            description: $model->description,
            startAt: new \DateTimeImmutable($model->start_at->toISOString()),
            endAt: new \DateTimeImmutable($model->end_at->toISOString()),
            color: $model->color ?? 'indigo',
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at->toISOString()) : null,
            updatedAt: $model->updated_at ? new \DateTimeImmutable($model->updated_at->toISOString()) : null,
        );
    }
}
