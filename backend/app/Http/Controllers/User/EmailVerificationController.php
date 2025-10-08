<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Controller;
use App\Models\User;

class EmailVerificationController extends Controller
{
    /**
     * 認証待ち表示（SPA側で表示するならリダイレクト）
     */
    public function showNotice()
    {
        return redirect('/email/verify/notice');
    }

    /**
     * 確認メールの再送
     */
    public function resendVerificationEmail(Request $request)
    {
        // 未ログインでも対応：メールアドレスが送信されていたらそれを使用
        $email = $request->input('email');

        // ログイン済みならそれを優先
        $user = $request->user() ?? ($email ? User::where('email', $email)->first() : null);

        if (! $user) {
            return response()->json(['error' => 'ユーザーが見つかりません。'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'すでに認証済みのメールアドレスです。'], 200);
        }

        // 確認メール送信
        $user->sendEmailVerificationNotification();

        return response()->json(['message' => '確認メールを再送しました。'], 200);
    }

    /**
     * メール内リンククリック時（認証処理）
     */
    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));

        if (! $user) {
            abort(404, 'ユーザーが見つかりません。');
        }

        // ハッシュ検証
        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            abort(403, '不正なリンクです。');
        }

        // すでに認証済みならそのままログイン画面へ
        if ($user->hasVerifiedEmail()) {
            return redirect(env('FRONTEND_URL') . '/login?verified=1');
        }

        // 認証完了処理
        $user->markEmailAsVerified();

        // SPA構成では自動ログインしない（Cookie共有不可のため）
        return redirect(env('FRONTEND_URL') . '/login?verified=1');
    }
}
