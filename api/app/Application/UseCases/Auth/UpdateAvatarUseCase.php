<?php

namespace App\Application\UseCases\Auth;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;

class UpdateAvatarUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(int $userId, string $avatarPath): User
    {
        return $this->userRepository->update($userId, ['avatar' => $avatarPath]);
    }
}
