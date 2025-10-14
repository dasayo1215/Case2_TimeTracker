<?php

namespace Tests\Feature\User;

use Tests\Feature\User\UserTestCase;
use App\Models\User;
use App\Notifications\VerifyEmailJa;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class EmailVerificationTest extends UserTestCase
{
    // 会員登録後、認証メールが送信される
    public function test_verification_email_is_sent_after_registration(): void
    {
        // 1. 会員登録をする
        Notification::fake();

        $payload = [
            'name'                  => 'テストユーザー',
            'email'                 => 'testuser@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);
        $response->assertStatus(200);

        $user = User::where('email', 'testuser@example.com')->first();
        $this->assertNotNull($user, 'ユーザーが作成されていません');

        // 2. 認証メールを送信する（VerifyEmailJa 通知が送られていることを確認）
        $sent = Notification::sent($user, VerifyEmailJa::class);
        $this->assertTrue(
            count($sent) > 0,
            '登録したメールアドレス宛に認証メールが送信されていません'
        );
    }

    // メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
    public function test_click_verification_button_redirects_to_verification_site(): void
    {
        // 1. メール認証導線画面を表示する
        $response = $this->get('/api/email/verify');
        $response->assertRedirect('/email/verify/notice');

        // 2. 「認証はこちらから」ボタンを押下（署名付きURL生成）
        $user = User::factory()->create(['email_verified_at' => null]);
        $hash = sha1($user->getEmailForVerification());
        $url = URL::signedRoute('verification.verify', [
            'id'   => $user->id,
            'hash' => $hash,
        ]);

        // 3. メール認証サイトを表示する
        $verifyResponse = $this->get($url);
        $verifyResponse->assertRedirectContains(env('FRONTEND_URL') . '/login?verified=1');
    }

    // メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
    public function test_email_verification_completes_and_redirects_to_attendance_form(): void
    {
        // 1. メール認証を完了する
        $user = User::factory()->create(['email_verified_at' => null]);
        $hash = sha1($user->getEmailForVerification());
        $url = URL::signedRoute('verification.verify', [
            'id'   => $user->id,
            'hash' => $hash,
        ]);

        $response = $this->get($url);
        $response->assertRedirectContains(env('FRONTEND_URL') . '/login?verified=1');

        // 認証済み確認
        $user->refresh();
        $this->assertNotNull($user->email_verified_at, 'メール認証が完了していません');

        // 2. 勤怠登録画面を表示する
        $this->actingAs($user, 'web');
        $attendanceResponse = $this->getJson('/api/attendance/status');
        $attendanceResponse->assertStatus(200);
    }
}
