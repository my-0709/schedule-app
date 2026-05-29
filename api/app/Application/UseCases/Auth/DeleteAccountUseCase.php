<?php

namespace App\Application\UseCases\Auth;

use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DeleteAccountUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(int $userId, string $password): void
    {
        $user = $this->userRepository->findById($userId);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['パスワードが正しくありません。'],
            ]);
        }

        $this->userRepository->delete($userId);
    }
}
