<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::guard('admin')->attempt($credentials)) {
            return $this->loginFailedResponse();
        }

        $user = Auth::guard('admin')->user();

        if ($user->role !== 'admin') {
            Auth::guard('admin')->logout();
            return $this->loginFailedResponse();
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => $user,
            'redirect' => '/admin/attendance/list',
        ]);
    }

    private function loginFailedResponse()
    {
        return response()->json([
            'errors' => ['email' => ['ログイン情報が登録されていません']]
        ], 422);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return response()->json([
            'message' => 'ログアウトしました',
        ]);
    }
}
