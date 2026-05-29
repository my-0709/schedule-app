<?php

namespace App\Application\UseCases\Auth;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;

class UpdateProfileUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(int $userId, string $name, string $email): User
    {
        return $this->userRepository->update($userId, [
            'name' => $name,
            'email' => $email,
        ]);
    }
}
