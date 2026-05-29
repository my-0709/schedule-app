<?php

namespace App\Presentation\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'color' => ['nullable', 'string', 'in:indigo,emerald,amber,rose,sky,violet'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルは必須です。',
            'start_at.required' => '開始日時は必須です。',
            'start_at.date' => '有効な日時を入力してください。',
            'end_at.required' => '終了日時は必須です。',
            'end_at.date' => '有効な日時を入力してください。',
            'end_at.after' => '終了日時は開始日時より後にしてください。',
        ];
    }
}
