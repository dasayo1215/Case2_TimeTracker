<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class EmailVerificationController extends Controller
{
    public function showNotice()
    {
        return redirect('/email/verify/notice');
    }

    public function resendVerificationEmail(Request $request)
    {
        $email = $request->input('email');
        $user = $request->user() ?? ($email ? User::where('email', $email)->first() : null);

        if (! $user) {
            return response()->json(['error' => 'ユーザーが見つかりません。'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'すでに認証済みのメールアドレスです。'], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => '確認メールを再送しました。'], 200);
    }

    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));

        if (! $user) {
            abort(404, 'ユーザーが見つかりません。');
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            abort(403, '不正なリンクです。');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(env('FRONTEND_URL') . '/login?verified=1');
        }

        $user->markEmailAsVerified();

        return redirect(env('FRONTEND_URL') . '/login?verified=1');
    }
}
