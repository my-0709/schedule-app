<?php

namespace App\Application\UseCases\Auth;

use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(int $userId, string $currentPassword, string $newPassword): void
    {
        $user = $this->userRepository->findById($userId);

        if (!$user || !Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['現在のパスワードが正しくありません。'],
            ]);
        }

        $this->userRepository->update($userId, [
            'password' => Hash::make($newPassword),
        ]);
    }
}
