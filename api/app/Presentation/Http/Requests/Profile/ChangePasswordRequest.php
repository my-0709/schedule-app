<?php

namespace App\Presentation\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => '現在のパスワードは必須です。',
            'new_password.required' => '新しいパスワードは必須です。',
            'new_password.min' => '新しいパスワードは8文字以上で入力してください。',
            'new_password.confirmed' => 'パスワードが一致しません。',
        ];
    }
}
