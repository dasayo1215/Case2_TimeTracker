<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CreatesNewUsers::class, CreateNewUser::class);
    }

    public function boot(): void
    {
        // Fortify のユーザー作成アクション設定
        Fortify::createUsersUsing(CreateNewUser::class);

        // メール認証画面（Bladeを使う場合）
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        // ログイン試行制限（1分あたり10回まで）
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

        /**
         * ✅ React SPA から /api/user を叩いたときに
         * 現在ログイン中のユーザー情報を返すよう明示的に定義。
         * （role を含む全属性を返す）
         */
        Route::middleware('web')->get('/api/user', function (Request $request) {
            return $request->user();
        });
    }
}
