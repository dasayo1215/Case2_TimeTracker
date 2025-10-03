<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users', 'string', 'max:255'],
            'password' => ['required', 'min:8', 'string'],
            'password_confirmation' => [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    if ($value !== $this->input('password')) {
                        $fail('パスワードと一致しません');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // 未入力
            'name.required' => 'お名前を入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
            'password_confirmation.required' => '確認用パスワードを入力してください',

            // メールアドレス関連
            'email.email' => '正しいメールアドレス形式で入力してください',
            'email.unique' => 'このメールアドレスは既に使用されています',

            // パスワード規則
            'password.min' => 'パスワードは8文字以上で入力してください',
        ];
    }
}
