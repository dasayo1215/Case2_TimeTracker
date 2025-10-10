<?php

namespace Tests\Feature\Admin\Auth;

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
        // 1. ユーザーを登録する（管理者）
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 2. メールアドレス以外のユーザー情報を入力する & 3. ログインの処理を行う
        $response = $this->postJson('/api/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // 期待挙動
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'メールアドレスを入力してください');
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_returns_error_when_password_is_empty(): void
    {
        // 1. ユーザーを登録する（管理者）
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 2. パスワード以外のユーザー情報を入力する & 3. ログインの処理を行う
        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        // 期待挙動
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
            ->assertJsonPath('errors.password.0', 'パスワードを入力してください');
    }

    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function test_returns_error_when_credentials_are_invalid(): void
    {
        // 1. ユーザーを登録する（管理者）
        User::factory()->create([
            'email' => 'correct@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 2. 誤ったメールアドレスのユーザー情報を入力する & 3. ログインの処理を行う
        $response = $this->postJson('/api/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        // 期待挙動
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'ログイン情報が登録されていません');
    }
}
