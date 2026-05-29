<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\User as UserEntity;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Models\User as UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?UserEntity
    {
        $model = UserModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $model = UserModel::where('email', $email)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function save(UserEntity $user): UserEntity
    {
        $model = UserModel::create([
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
        ]);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): UserEntity
    {
        $model = UserModel::findOrFail($id);
        $model->update($data);
        $model->refresh();

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        UserModel::findOrFail($id)->delete();
    }

    private function toEntity(UserModel $model): UserEntity
    {
        return new UserEntity(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            password: $model->password,
            avatar: $model->avatar,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at->toISOString()) : null,
            updatedAt: $model->updated_at ? new \DateTimeImmutable($model->updated_at->toISOString()) : null,
        );
    }
}
