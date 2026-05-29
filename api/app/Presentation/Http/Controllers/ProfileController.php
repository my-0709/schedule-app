<?php

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Auth\ChangePasswordUseCase;
use App\Application\UseCases\Auth\DeleteAccountUseCase;
use App\Application\UseCases\Auth\UpdateAvatarUseCase;
use App\Application\UseCases\Auth\UpdateProfileUseCase;
use App\Presentation\Http\Requests\Profile\ChangePasswordRequest;
use App\Presentation\Http\Requests\Profile\UpdateProfileRequest;
use App\Presentation\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UpdateProfileUseCase $updateProfileUseCase,
        private readonly ChangePasswordUseCase $changePasswordUseCase,
        private readonly DeleteAccountUseCase $deleteAccountUseCase,
        private readonly UpdateAvatarUseCase $updateAvatarUseCase,
    ) {}

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->updateProfileUseCase->execute(
            userId: $request->user()->id,
            name: $request->input('name'),
            email: $request->input('email'),
        );

        return response()->json(new UserResource($user));
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ], [
            'avatar.required' => '画像ファイルを選択してください。',
            'avatar.image' => '画像ファイルを選択してください。',
            'avatar.max' => '画像サイズは2MB以下にしてください。',
        ]);

        $currentUser = $request->user();

        if ($currentUser->avatar) {
            Storage::disk('public')->delete($currentUser->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user = $this->updateAvatarUseCase->execute($currentUser->id, $path);

        return response()->json(new UserResource($user));
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->changePasswordUseCase->execute(
            userId: $request->user()->id,
            currentPassword: $request->input('current_password'),
            newPassword: $request->input('new_password'),
        );

        return response()->json(['message' => 'パスワードを変更しました。']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $this->deleteAccountUseCase->execute(
            userId: $request->user()->id,
            password: $request->input('password'),
        );

        return response()->json(['message' => '退会しました。']);
    }
}
