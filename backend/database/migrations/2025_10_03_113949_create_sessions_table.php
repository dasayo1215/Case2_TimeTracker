<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| sessions テーブルについて
|--------------------------------------------------------------------------
| このテーブルは Laravel のセッション管理に使用されます。
|
| - .env の SESSION_DRIVER=database の場合に必須となる。
| - ユーザーがログインした状態を維持するための情報が保存される。
| - Fortify での認証（Auth::login など）も、このセッションを介して
|   「ログイン済みユーザー」として判定される。
| - 今後、JWT や Sanctum に移行する場合は不要になる可能性があるが、
|   現状はセッション方式の認証を採用しているため必須。
|
*/

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
