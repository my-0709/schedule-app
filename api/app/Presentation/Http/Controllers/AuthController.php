<?php

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\Auth\LoginDTO;
use App\Application\DTOs\Auth\RegisterDTO;
use App\Application\UseCases\Auth\LoginUseCase;
use App\Application\UseCases\Auth\RegisterUseCase;
use App\Infrastructure\Models\User;
use App\Presentation\Http\Requests\Auth\LoginRequest;
use App\Presentation\Http\Requests\Auth\RegisterRequest;
use App\Presentation\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterUseCase $registerUseCase,
        private readonly LoginUseCase $loginUseCase,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = new RegisterDTO(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password'),
        );

        $user = $this->registerUseCase->execute($dto);

        $userModel = User::find($user->id);
        $token = $userModel->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $dto = new LoginDTO(
            email: $request->input('email'),
            password: $request->input('password'),
        );

        $user = $this->loginUseCase->execute($dto);

        $userModel = User::find($user->id);
        $userModel->tokens()->delete();
        $token = $userModel->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'ログアウトしました。']);
    }

    public function me(Request $request): JsonResponse
    {
        $userModel = $request->user();

        return response()->json([
            'id' => $userModel->id,
            'name' => $userModel->name,
            'email' => $userModel->email,
            'avatar' => $userModel->avatar
                ? Storage::disk('public')->url($userModel->avatar)
                : null,
        ]);
    }
}
