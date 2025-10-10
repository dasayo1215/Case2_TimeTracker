<?php

namespace Tests\Feature\User\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    // 名前が未入力の場合、バリデーションメッセージが表示される
    public function test_returns_error_when_name_is_empty(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name'])
                ->assertJsonPath('errors.name.0', 'お名前を入力してください');
    }

    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_returns_error_when_email_is_empty(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email'])
                ->assertJsonPath('errors.email.0', 'メールアドレスを入力してください');
    }

    // パスワードが8文字未満の場合、バリデーションメッセージが表示される
    public function test_returns_error_when_password_is_too_short(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password'])
                ->assertJsonPath('errors.password.0', 'パスワードは8文字以上で入力してください');
    }

    // パスワードが一致しない場合、バリデーションメッセージが表示される
    public function test_returns_error_when_password_confirmation_does_not_match(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password_confirmation'])
                ->assertJsonPath('errors.password_confirmation.0', 'パスワードと一致しません');
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_returns_error_when_password_is_empty(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password'])
                ->assertJsonPath('errors.password.0', 'パスワードを入力してください');
    }

    // フォームに内容が入力されていた場合、データが正常に保存される
    public function test_registers_user_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
    }
}
