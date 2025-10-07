<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Facades\Auth;
use App\Actions\Fortify\CreateNewUser;

class AuthController extends Controller
{
    // 会員登録
    public function register(RegisterRequest $request, CreatesNewUsers $creator)
    {
        $data = $request->validated();

        $user = $creator->create($data);

        Auth::login($user);

        return response()->json([
            'message' => '会員登録が完了しました',
            'user' => $user,
        ]);
    }

    // ログイン
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return $this->loginFailedResponse();
        }

        $user = Auth::user();

        if ($user->role !== 'user') {
            Auth::logout();
            return $this->loginFailedResponse();
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
