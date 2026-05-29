<?php

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\Auth\RegisterDTO;
use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class RegisterUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(RegisterDTO $dto): User
    {
        $user = new User(
            id: null,
            name: $dto->name,
            email: $dto->email,
            password: Hash::make($dto->password),
        );

        return $this->userRepository->save($user);
    }
}
