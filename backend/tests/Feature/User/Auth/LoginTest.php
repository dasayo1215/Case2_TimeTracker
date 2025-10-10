<?php

namespace Tests\Feature\User\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_returns_error_when_email_is_empty(): void
    {
        // 1. ユーザーを登録する
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. メールアドレス以外の情報を入力してログイン処理
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // 3. 期待挙動を確認
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email'])
                ->assertJsonPath('errors.email.0', 'メールアドレスを入力してください');
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_returns_error_when_password_is_empty(): void
    {
        // 1. ユーザーを登録する
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. パスワード以外の情報を入力してログイン処理
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        // 3. 期待挙動を確認
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password'])
                ->assertJsonPath('errors.password.0', 'パスワードを入力してください');
    }

    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function test_returns_error_when_credentials_are_invalid(): void
    {
        // 1. ユーザーを登録する
        User::factory()->create([
            'email' => 'correct@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. 誤ったメールアドレスでログイン処理
        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        // 3. 期待挙動を確認
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email'])
                ->assertJsonPath('errors.email.0', 'ログイン情報が登録されていません');
    }
}
