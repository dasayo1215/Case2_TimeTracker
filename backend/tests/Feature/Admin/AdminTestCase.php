<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

abstract class AdminTestCase extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User */
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // role = admin のユーザーを作成
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 管理者認証ガードを使用してログイン状態にする
        $this->actingAs($this->admin, 'admin');
    }

    /**
     * 管理者としてGETリクエストを送る共通メソッド
     */
    protected function getJsonAsAdmin(string $uri, array $params = [])
    {
        return $this->getJson($uri, $params);
    }

    /**
     * 管理者としてPOSTリクエストを送る共通メソッド
     */
    protected function postJsonAsAdmin(string $uri, array $data = [])
    {
        return $this->postJson($uri, $data);
    }
}
