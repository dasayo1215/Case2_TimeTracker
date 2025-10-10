<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Facades\Auth;
use App\Actions\Fortify\CreateNewUser;
use App\Models\User;

class AuthController extends Controller
{
    // 会員登録
    public function register(RegisterRequest $request, CreatesNewUsers $creator)
    {
        $data = $request->validated();

        // 既存ユーザーの確認
        $existing = User::where('email', $data['email'])->first();

        if ($existing) {
            if ($existing->hasVerifiedEmail()) {
                // 認証済み
                return response()->json([
                    'errors' => ['email' => ['このメールアドレスは既に登録されています。']],
                ], 422);
            } else {
                // 認証未完了 → メールを再送
                $existing->sendEmailVerificationNotification();

                return response()->json([
                    'message' => '以前登録されたメールアドレスが未認証のため、認証メールを再送しました。',
                    'resend' => true,
                ]);
            }
        }

        // 新規ユーザー作成
        $user = $creator->create($data);

        // 認証メール送信
        $user->sendEmailVerificationNotification();

        \Auth::login($user);

        return response()->json([
            'message' => '登録していただいたメールアドレスに認証メールを送付しました。',
            'user' => $user,
        ], 200);
    }

    // ログイン
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return $this->loginFailedResponse();
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 一般ユーザー以外は弾く
        if ($user->role !== 'user') {
            Auth::logout();
            return $this->loginFailedResponse();
        }

        // ★ メール未認証ならメールを再送して案内
        if (!$user->hasVerifiedEmail()) {
            // セッションは破棄しておく
            Auth::logout();

            // 再送
            $user->sendEmailVerificationNotification();

            return response()->json([
                'errors' => [
                    'email' => ['メールアドレスの認証が完了していません。認証メールを再送しました。']
                ],
                'need_verification' => true,
            ], 403);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => $user,
            'redirect' => '/attendance',
        ]);
    }


    // ログアウト
    public function logout()
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return response()->json([
            'message' => 'ログアウトしました',
        ]);
    }

    private function loginFailedResponse()
    {
        return response()->json([
            'errors' => [
                'email' => ['ログイン情報が登録されていません']
            ]
        ], 422);
    }
}
